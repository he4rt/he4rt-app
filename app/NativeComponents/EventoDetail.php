<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use App\Support\EventDateFormatter;
use App\Support\EventPresenter;
use App\Support\RefreshesExpiredToken;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class EventoDetail extends NativeComponent
{
    use RefreshesExpiredToken;

    /** Bound from the `/eventos/{eventId}` route segment. */
    public string $eventId = '';

    public string $status = 'loading';

    public string $title = '';

    public ?string $description = null;

    public string $typeLabel = '';

    public ?string $location = null;

    public string $when = '';

    public ?string $methodLabel = null;

    /** Raw enrollment_method (rsvp|rsvp_checkin|application) — decides which action to offer. */
    public ?string $method = null;

    public ?string $enrollmentStatusLabel = null;

    public string $enrollmentStatusToken = 'primary';

    public bool $canApply = false;

    public bool $canConfirmPresence = false;

    public bool $isEventFull = false;

    public bool $canCheckIn = false;

    public bool $isEnrolling = false;

    public ?string $enrollError = null;

    public bool $showCheckIn = false;

    public string $checkInCode = '';

    public bool $isCheckingIn = false;

    public ?string $checkInError = null;

    /**
     * Staff-only (super admin). Fetched alongside the event detail —
     * same two-separate-requests pattern Perfil uses for identity vs.
     * profile, so a failure here never blocks the main screen.
     */
    public bool $isSuperAdmin = false;

    public int $codeDigits = 6;

    public bool $isGeneratingCode = false;

    public ?string $generateCodeError = null;

    public ?string $generatedCode = null;

    public ?string $generatedCodeExpiresAtLabel = null;

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->event($t, $this->eventId));

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->status = 'error';

            return;
        }

        $this->status = 'ok';
        $this->applyEvent($response->json('data', []));

        $this->loadStaffStatus($token);
    }

    public function enroll(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $this->isEnrolling = true;
        $this->enrollError = null;

        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->enrollInEvent($t, $this->eventId));

        $this->isEnrolling = false;

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->enrollError = $response->json('message', 'Não foi possível confirmar sua presença. Tente novamente.');

            return;
        }

        $this->refresh();
    }

    public function startCheckIn(): void
    {
        $this->showCheckIn = true;
        $this->checkInCode = '';
        $this->checkInError = null;
    }

    public function cancelCheckIn(): void
    {
        $this->showCheckIn = false;
        $this->checkInCode = '';
        $this->checkInError = null;
    }

    public function submitCheckIn(): void
    {
        if (!preg_match('/^(?:\d{4}|\d{6})$/', $this->checkInCode)) {
            $this->checkInError = 'O código tem 4 ou 6 dígitos.';

            return;
        }

        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $this->isCheckingIn = true;
        $this->checkInError = null;

        $api = new He4rtApi;
        $code = $this->checkInCode;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->checkIntoEvent($t, $this->eventId, $code));

        $this->isCheckingIn = false;

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->checkInError = $response->json('message', 'Código inválido. Confira e tente de novo.');

            return;
        }

        $this->showCheckIn = false;
        $this->refresh();
    }

    public function setCodeDigits(int $digits): void
    {
        $this->codeDigits = $digits;
    }

    public function generateCheckInCode(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $this->isGeneratingCode = true;
        $this->generateCodeError = null;

        $api = new He4rtApi;
        $digits = $this->codeDigits;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->generateCheckInCode($t, $this->eventId, $digits));

        $this->isGeneratingCode = false;

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->generateCodeError = 'Não foi possível gerar o código. Tente novamente.';

            return;
        }

        $this->generatedCode = $response->json('code');
        $this->generatedCodeExpiresAtLabel = Carbon::parse($response->json('expires_at'))->format('H:i');
    }

    public function dismissGeneratedCode(): void
    {
        $this->generatedCode = null;
        $this->generatedCodeExpiresAtLabel = null;
    }

    public function navTitle(): string
    {
        return $this->title !== '' ? $this->title : 'Evento';
    }

    public function render(): View
    {
        return view('native.evento-detail');
    }

    /** @param array<string, mixed> $event */
    private function applyEvent(array $event): void
    {
        $enrollment = $event['enrollment'] ?? null;

        $this->title = $event['title'];
        $this->description = $event['description'];
        $this->typeLabel = EventPresenter::typeLabel($event['event_type']);
        $this->location = $event['location'];
        $this->when = EventDateFormatter::when($event['starts_at']);
        $this->method = $event['enrollment_method'] ?? null;
        $this->methodLabel = EventPresenter::methodLabel($this->method);
        $this->canApply = (bool) $event['can_apply'];
        $this->canConfirmPresence = (bool) $event['can_confirm_presence'];
        $this->isEventFull = (bool) $event['is_event_full'];

        $enrollmentStatus = $enrollment['status'] ?? null;
        $this->enrollmentStatusLabel = EventPresenter::enrollmentLabel($enrollmentStatus);
        $this->enrollmentStatusToken = EventPresenter::enrollmentToken($enrollmentStatus);
        $this->canCheckIn = $enrollmentStatus === 'confirmed';
    }

    /**
     * Failure here (expired refresh included) never bounces to Login or
     * blocks the screen — worst case a staff member just doesn't see the
     * staff section until the next successful refresh.
     */
    private function loadStaffStatus(string $token): void
    {
        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->me($t));

        $this->isSuperAdmin = $response !== null && $response->successful() && (bool) $response->json('is_super_admin', false);
    }
}
