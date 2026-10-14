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

class Eventos extends NativeComponent
{
    use RefreshesExpiredToken;

    public string $status = 'loading';

    /**
     * @var list<array{id: string, title: string, typeLabel: string, when: string, location: ?string, statusLabel: ?string, statusToken: string}>
     */
    public array $upcomingEvents = [];

    /** @var list<array{id: string, title: string, typeLabel: string, when: string, location: ?string, statusLabel: ?string, statusToken: string}> */
    public array $pastEvents = [];

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
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->events($t));

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->status = 'error';

            return;
        }

        $this->status = 'ok';

        $rawEvents = collect($response->json('data', []));

        $this->upcomingEvents = $rawEvents
            ->filter(fn (array $event): bool => $this->isUpcoming($event))
            ->sortBy('starts_at')
            ->map(fn (array $event): array => $this->mapEvent($event))
            ->values()
            ->all();

        $this->pastEvents = $rawEvents
            ->reject(fn (array $event): bool => $this->isUpcoming($event))
            ->sortByDesc('starts_at')
            ->map(fn (array $event): array => $this->mapEvent($event))
            ->values()
            ->all();
    }

    public function goToEvent(string $eventId): void
    {
        $this->navigate("/eventos/{$eventId}");
    }

    public function navTitle(): string
    {
        return 'Eventos';
    }

    public function render(): View
    {
        return view('native.eventos');
    }

    /** @param array<string, mixed> $event */
    private function isUpcoming(array $event): bool
    {
        return $event['status'] === 'published' && Carbon::parse($event['starts_at'])->isFuture();
    }

    /** @param array<string, mixed> $event */
    private function mapEvent(array $event): array
    {
        return [
            'id' => (string) $event['id'],
            'title' => $event['title'],
            'typeLabel' => EventPresenter::typeLabel($event['event_type']),
            'when' => EventDateFormatter::when($event['starts_at']),
            'location' => $event['location'],
            'statusLabel' => EventPresenter::enrollmentLabel($event['enrollment_status'] ?? null),
            'statusToken' => EventPresenter::enrollmentToken($event['enrollment_status'] ?? null),
        ];
    }
}
