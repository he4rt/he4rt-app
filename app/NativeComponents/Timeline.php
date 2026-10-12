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

class Timeline extends NativeComponent
{
    use RefreshesExpiredToken;

    public string $status = 'loading';

    public ?string $nextPageUrl = null;

    public bool $isLoadingMore = false;

    /**
     * @var list<array{id: string, author: string, avatarUrl: ?string, timeAgo: string, content: string, image: ?string, repliesCount: int, likesCount: int, likedByMe: bool}>
     */
    public array $posts = [];

    /**
     * Id (UUID) of the post the reply composer (bottom sheet) is open
     * for, or null when closed. There's no endpoint to list a post's
     * existing replies (POST/DELETE only) — composing one just bumps the
     * visible count, it doesn't render into a thread.
     */
    public ?string $replyingToPostId = null;

    public string $replyContent = '';

    public bool $isSubmittingReply = false;

    public ?string $replyError = null;

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

    public function startReply(string $postId): void
    {
        $this->replyingToPostId = $postId;
        $this->replyContent = '';
        $this->replyError = null;
    }

    public function cancelReply(): void
    {
        $this->replyingToPostId = null;
        $this->replyContent = '';
        $this->replyError = null;
    }

    public function submitReply(): void
    {
        if ($this->replyingToPostId === null || mb_trim($this->replyContent) === '') {
            return;
        }

        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $postId = $this->replyingToPostId;
        $content = $this->replyContent;

        $this->isSubmittingReply = true;
        $this->replyError = null;

        $api = new He4rtApi;
        $response = $this->withTokenRetry($token, fn (string $t): Response => $api->replyToPost($t, $postId, $content));

        $this->isSubmittingReply = false;

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if ($response->status() === 422) {
            $this->replyError = $response->json('errors.content.0', 'Não foi possível enviar sua resposta.');

            return;
        }

        if (!$response->successful()) {
            $this->replyError = 'Não foi possível enviar sua resposta. Tente novamente.';

            return;
        }

        $this->bumpReplyCount($postId);
        $this->cancelReply();
    }

    public function toggleLike(string $postId): void
    {
        $post = $this->findPost($postId);

        if ($post === null) {
            return;
        }

        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $api = new He4rtApi;
        $wasLiked = $post['likedByMe'];

        $response = $this->withTokenRetry(
            $token,
            fn (string $t): Response => $wasLiked ? $api->unlikePost($t, $postId) : $api->likePost($t, $postId),
        );

        if ($response === null) {
            $this->replace('/login');

            return;
        }

        if (!$response->successful()) {
            return;
        }

        $this->updatePost($postId, [
            'likesCount' => (int) $response->json('likes_count', $post['likesCount']),
            'likedByMe' => (bool) $response->json('liked_by_me', !$wasLiked),
        ]);
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

        return $this->withTokenRetry($token, fn (string $t): Response => $api->timeline($t, $url));
    }

    private function bumpReplyCount(string $postId): void
    {
        $this->updatePost($postId, [
            'repliesCount' => $this->findPost($postId)['repliesCount'] + 1,
        ]);
    }

    private function findPost(string $postId): ?array
    {
        foreach ($this->posts as $post) {
            if ($post['id'] === $postId) {
                return $post;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $changes */
    private function updatePost(string $postId, array $changes): void
    {
        $this->posts = array_map(
            fn (array $post): array => $post['id'] === $postId ? [...$post, ...$changes] : $post,
            $this->posts,
        );
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
            'id' => (string) $post['id'],
            'author' => $post['author']['username'],
            'avatarUrl' => $post['author']['avatar_url'] ?? null,
            'timeAgo' => Carbon::parse($post['created_at'])->diffForHumans(),
            'content' => $post['content'],
            'image' => $post['images'][0] ?? null,
            'repliesCount' => $post['replies_count'] ?? 0,
            'likesCount' => $post['likes_count'] ?? 0,
            'likedByMe' => $post['liked_by_me'] ?? false,
        ];
    }
}
