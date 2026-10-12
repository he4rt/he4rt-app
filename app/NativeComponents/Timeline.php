<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Timeline extends NativeComponent
{
    public string $status = 'loading';

    /**
     * @var list<array{id: int, author: string, avatarUrl: ?string, timeAgo: string, content: string, image: ?string, repliesCount: int, reactionsCount: int}>
     */
    public array $posts = [];

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

        $this->loadFeed($token);
    }

    public function navTitle(): string
    {
        return 'Timeline';
    }

    public function render(): View
    {
        return view('native.timeline');
    }

    private function loadFeed(string $token): void
    {
        $api = new He4rtApi;
        $response = $api->timeline($token);

        if ($response->unauthorized()) {
            $token = $this->refreshToken($api, $token);

            if ($token === null) {
                $this->replace('/login');

                return;
            }

            $response = $api->timeline($token);
        }

        if (!$response->successful()) {
            $this->status = 'error';

            return;
        }

        $this->status = 'ok';
        $this->posts = collect($response->json('data', []))
            ->map(fn (array $post): array => $this->mapPost($post))
            ->all();
    }

    /** @param array<string, mixed> $post */
    private function mapPost(array $post): array
    {
        return [
            'id' => $post['id'],
            'author' => $post['author']['username'],
            'avatarUrl' => $post['author']['avatar_url'] ?? null,
            'timeAgo' => Carbon::parse($post['created_at'])->diffForHumans(),
            'content' => $post['content'],
            'image' => $post['images'][0] ?? null,
            'repliesCount' => $post['replies_count'] ?? 0,
            'reactionsCount' => $post['reactions_count'] ?? 0,
        ];
    }

    /** Null means the token could not be refreshed — caller should log out. */
    private function refreshToken(He4rtApi $api, string $expiredToken): ?string
    {
        $response = $api->refresh($expiredToken);

        if (!$response->successful()) {
            (new AuthTokenStore)->clear();

            return null;
        }

        $accessToken = (string) $response->json('access_token');
        (new AuthTokenStore)->put($accessToken, (int) $response->json('expires_in'));

        return $accessToken;
    }
}
