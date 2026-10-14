<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use App\Support\EventDateFormatter;
use App\Support\EventPresenter;
use App\Support\RefreshesExpiredToken;
use Illuminate\Http\Client\Response;
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
}
