<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use App\Support\RefreshesExpiredToken;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Browser;

class Perfil extends NativeComponent
{
    use RefreshesExpiredToken;

    /**
     * Mirrors He4rt\Profile\Enums\SeniorityLevel's labels
     * (heartdevs.com app-modules/profile/lang/pt_BR/enums.php) — this app
     * has no access to that lang file, so the mapping is duplicated here.
     */
    private const SENIORITY_LABELS = [
        'junior' => 'Júnior',
        'mid' => 'Pleno',
        'senior' => 'Sênior',
        'specialist' => 'Especialista',
        'lead' => 'Lead',
    ];

    private const START_AVAILABILITY_LABELS = [
        'immediate' => 'Imediato',
        '1_week' => '1 semana',
        '2_weeks' => '2 semanas',
        '3_weeks' => '3 semanas',
        '1_month' => '1 mês',
        '2_months' => '2 meses',
        'negotiable' => 'Negociável',
    ];

    private const SKILL_PROFICIENCY_LABELS = [
        'beginner' => 'Básico',
        'intermediate' => 'Intermediário',
        'advanced' => 'Avançado',
        'expert' => 'Especialista',
    ];

    /** Mirrors He4rt\Profile\Enums\SocialPlatform::getUrl(). */
    private const SOCIAL_URL_PREFIXES = [
        'instagram' => 'https://instagram.com/',
        'twitter' => 'https://x.com/',
        'linkedin' => 'https://linkedin.com/in/',
        'youtube' => 'https://youtube.com/@',
        'bluesky' => 'https://bsky.app/profile/',
    ];

    private const SOCIAL_LABELS = [
        'instagram' => 'Instagram',
        'twitter' => 'Twitter',
        'website' => 'Website',
        'youtube' => 'YouTube',
        'bluesky' => 'Bluesky',
        'linkedin' => 'LinkedIn',
    ];

    private const MONTH_ABBREVIATIONS = [
        1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr',
        5 => 'mai', 6 => 'jun', 7 => 'jul', 8 => 'ago',
        9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez',
    ];

    public string $status = 'loading';

    public ?string $username = null;

    public ?string $avatarUrl = null;

    public ?string $headline = null;

    public ?string $about = null;

    public ?string $seniorityLabel = null;

    public ?int $yearsExperience = null;

    public bool $availableForProposals = false;

    public ?string $startAvailabilityLabel = null;

    /** @var list<array{label: string, url: string}> */
    public array $socialLinks = [];

    /** @var list<string> */
    public array $skills = [];

    /** @var list<array{company: string, position: string, period: string, description: ?string}> */
    public array $workExperiences = [];

    public function mount(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $this->loadIdentity($token);

        if ($this->status === 'ok') {
            $this->loadProfileDetails($token);
        }
    }

    public function logout(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token !== null) {
            (new He4rtApi)->logout($token);
        }

        (new AuthTokenStore)->clear();
        $this->replace('/login');
    }

    public function openSocialLink(string $url): void
    {
        Browser::open($url);
    }

    public function navTitle(): string
    {
        return 'Perfil';
    }

    public function render(): View
    {
        return view('native.perfil');
    }

    private function loadIdentity(string $token): void
    {
        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->me($t));

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->status = 'error';

            return;
        }

        $this->status = 'ok';
        $this->username = $response->json('username');
        $this->avatarUrl = $response->json('avatar_url');
    }

    /**
     * The extended profile (headline, skills, work experience) is a
     * separate endpoint from identity — its failure shouldn't block
     * viewing your own account/logging out, so it fails silently,
     * leaving those sections empty rather than flipping $status.
     */
    private function loadProfileDetails(string $token): void
    {
        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->profile($t));

        if ($response === null || !$response->successful()) {
            return;
        }

        $profile = $response->json('data', []);

        $this->headline = $profile['headline'] ?? null;
        $this->about = $profile['about'] ?? null;
        $this->seniorityLabel = self::SENIORITY_LABELS[$profile['seniority_level'] ?? ''] ?? null;
        $this->yearsExperience = $profile['years_experience'] ?? null;
        $this->availableForProposals = (bool) ($profile['available_for_proposals'] ?? false);
        $this->startAvailabilityLabel = self::START_AVAILABILITY_LABELS[$profile['start_availability'] ?? ''] ?? null;
        $this->socialLinks = $this->mapSocialLinks($profile['social_links'] ?? []);
        $this->skills = $this->mapSkills($profile['skills'] ?? []);
        $this->workExperiences = $this->mapWorkExperiences($profile['work_experiences'] ?? []);
    }

    /**
     * @param  array<string, string>  $rawLinks
     * @return list<array{label: string, url: string}>
     */
    private function mapSocialLinks(array $rawLinks): array
    {
        $links = [];

        foreach ($rawLinks as $platform => $handle) {
            if ($handle === null || $handle === '') {
                continue;
            }

            $links[] = [
                'label' => self::SOCIAL_LABELS[$platform] ?? $platform,
                'url' => $this->buildSocialUrl($platform, $handle),
            ];
        }

        return $links;
    }

    private function buildSocialUrl(string $platform, string $handle): string
    {
        if (str_starts_with($handle, 'http://') || str_starts_with($handle, 'https://')) {
            return $handle;
        }

        $slug = mb_ltrim($handle, '@');
        $prefix = self::SOCIAL_URL_PREFIXES[$platform] ?? null;

        if ($prefix === null) {
            // 'website' has no fixed prefix — mirrors SocialPlatform::Website.
            return "https://{$slug}";
        }

        return $prefix.$slug;
    }

    /**
     * @param  list<array{name: string, proficiency: string}>  $rawSkills
     * @return list<string>
     */
    private function mapSkills(array $rawSkills): array
    {
        return collect($rawSkills)
            ->map(function (array $skill): string {
                $proficiency = self::SKILL_PROFICIENCY_LABELS[$skill['proficiency']] ?? null;

                return $proficiency !== null ? "{$skill['name']} · {$proficiency}" : $skill['name'];
            })
            ->all();
    }

    /**
     * @param  list<array{company_name: string, position: string, description: ?string, start_date: string, end_date: ?string, is_currently_working_here: bool}>  $rawExperiences
     * @return list<array{company: string, position: string, period: string, description: ?string}>
     */
    private function mapWorkExperiences(array $rawExperiences): array
    {
        return collect($rawExperiences)
            ->map(fn (array $experience): array => [
                'company' => $experience['company_name'],
                'position' => $experience['position'],
                'description' => $experience['description'],
                'period' => $this->formatPeriod(
                    $experience['start_date'],
                    $experience['end_date'],
                    $experience['is_currently_working_here'],
                ),
            ])
            ->all();
    }

    private function formatPeriod(string $start, ?string $end, bool $current): string
    {
        $startLabel = $this->formatMonthYear($start);

        if ($current) {
            return "{$startLabel} — atual";
        }

        return $end === null ? $startLabel : "{$startLabel} — {$this->formatMonthYear($end)}";
    }

    private function formatMonthYear(string $date): string
    {
        $parsed = Carbon::parse($date);

        return self::MONTH_ABBREVIATIONS[$parsed->month].'/'.$parsed->format('Y');
    }
}
