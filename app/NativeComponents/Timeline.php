<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Timeline extends NativeComponent
{
    public string $status = 'loading';

    public ?string $nextPageUrl = null;

    public bool $isLoadingMore = false;

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

        $response = $this->fetchPage($token);

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            $this->status = 'error';

            return;
        }

        $this->status = 'ok';
        $this->posts = $this->mapPosts($response->json('data', []));
        $this->nextPageUrl = $response->json('links.next');
    }

    public function loadMore(): void
    {
        if ($this->nextPageUrl === null || $this->isLoadingMore) {
            return;
        }

        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $this->isLoadingMore = true;
        $response = $this->fetchPage($token, $this->nextPageUrl);
        $this->isLoadingMore = false;

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            return;
        }

        $this->posts = [...$this->posts, ...$this->mapPosts($response->json('data', []))];
        $this->nextPageUrl = $response->json('links.next');
    }

    public function navTitle(): string
    {
        return 'Timeline';
    }

    public function render(): View
    {
        return view('native.timeline');
    }

    /**
     * Fetches a page, retrying once with a refreshed token on a 401. Null
     * means the session is gone (refresh also failed) — caller should
     * bounce to Login.
     */
    private function fetchPage(string $token, ?string $url = null): ?Response
    {
        $api = new He4rtApi;
        $response = $api->timeline($token, $url);

        if (!$response->unauthorized()) {
            return $response;
        }

        $refreshedToken = $this->refreshToken($api, $token);

        if ($refreshedToken === null) {
            return null;
        }

        return $api->timeline($refreshedToken, $url);
    }

    /**
     * @param  list<array<string, mixed>>  $rawPosts
     * @return list<array<string, mixed>>
     */
    private function mapPosts(array $rawPosts): array
    {
        return collect($rawPosts)->map(fn (array $post): array => $this->mapPost($post))->all();
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
