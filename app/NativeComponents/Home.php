<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use App\Support\RefreshesExpiredToken;
use App\Support\TimelinePostMapper;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Browser;

class Home extends NativeComponent
{
    use RefreshesExpiredToken;

    private const WEEKDAY_ABBREVIATIONS = [
        0 => 'Dom', 1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sáb',
    ];

    private const MONTH_ABBREVIATIONS = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
        5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
    ];

    /** Mirrors He4rt\Events\Enrollment\Enums\EnrollmentStatus's labels. */
    private const ENROLLMENT_LABELS = [
        'pending' => 'Inscrição pendente',
        'confirmed' => 'Inscrito',
        'waitlisted' => 'Lista de espera',
        'checked_in' => 'Inscrito',
        'attended' => 'Inscrito',
    ];

    private const ENROLLMENT_TOKENS = [
        'pending' => 'primary',
        'confirmed' => 'success',
        'waitlisted' => 'secondary',
        'checked_in' => 'success',
        'attended' => 'success',
    ];

    /**
     * Mock — no API for community-wide stats (members/online/messages)
     * exists anywhere in heartdevs.com; confirmed by reading every
     * app-modules/*\/routes/api-mobile-routes.php file in that repo.
     *
     * @var array{membros: string, online: string, mensagens: string}
     */
    public array $stats = [
        'membros' => '9.2k+',
        'online' => '184',
        'mensagens' => '1.3k',
    ];

    public string $eventsStatus = 'loading';

    /**
     * @var list<array{id: string, title: string, when: string, status: string, statusToken: string}>
     */
    public array $upcomingEvents = [];

    public string $postsStatus = 'loading';

    /** @var array{id: string, author: string, avatarUrl: ?string, timeAgo: string, content: string, image: ?string, repliesCount: int, likesCount: int, likedByMe: bool}|null */
    public ?array $featuredPost = null;

    /** @var list<array{id: string, title: string, author: string, timeAgo: string}> */
    public array $latestPosts = [];

    public function mount(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $this->loadEvents($token);

        // loadEvents() may have already bounced to Login (dead session) —
        // don't burn a second round-trip finding that out again.
        if ($this->getNavigationIntent() !== null) {
            return;
        }

        $this->loadPosts($token);
    }

    public function goToEventos(): void
    {
        $this->replace('/eventos');
    }

    public function goToTimeline(): void
    {
        $this->replace('/timeline');
    }

    public function goToDiscord(): void
    {
        Browser::open('https://discord.com/invite/he4rt');
    }

    public function navTitle(): string
    {
        return 'Início';
    }

    public function render(): View
    {
        return view('native.home');
    }

    private function loadEvents(string $token): void
    {
        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->events($t));

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->eventsStatus = 'error';

            return;
        }

        $this->eventsStatus = 'ok';
        $this->upcomingEvents = collect($response->json('data', []))
            ->filter(fn (array $event): bool => $event['status'] === 'published' && Carbon::parse($event['starts_at'])->isFuture())
            ->take(3)
            ->map(fn (array $event): array => $this->mapEvent($event))
            ->values()
            ->all();
    }

    private function loadPosts(string $token): void
    {
        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->timeline($t));

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->postsStatus = 'error';

            return;
        }

        $this->postsStatus = 'ok';

        $posts = collect($response->json('data', []));
        $this->featuredPost = $posts->isNotEmpty() ? TimelinePostMapper::map($posts->first()) : null;
        $this->latestPosts = $posts->skip(1)->take(2)
            ->map(fn (array $post): array => [
                'id' => (string) $post['id'],
                'title' => $post['content'],
                'author' => $post['author']['username'],
                'timeAgo' => Carbon::parse($post['created_at'])->diffForHumans(),
            ])
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $event */
    private function mapEvent(array $event): array
    {
        $enrollmentStatus = $event['enrollment_status'] ?? null;

        return [
            'id' => (string) $event['id'],
            'title' => $event['title'],
            'when' => $this->formatEventWhen($event['starts_at']),
            'status' => self::ENROLLMENT_LABELS[$enrollmentStatus] ?? 'Em breve',
            'statusToken' => self::ENROLLMENT_TOKENS[$enrollmentStatus] ?? 'primary',
        ];
    }

    private function formatEventWhen(string $startsAt): string
    {
        $parsed = Carbon::parse($startsAt);
        $weekday = self::WEEKDAY_ABBREVIATIONS[$parsed->dayOfWeek];
        $month = self::MONTH_ABBREVIATIONS[$parsed->month];

        return "{$weekday}, {$parsed->day} {$month} · {$parsed->format('H')}h";
    }
}
