<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Maps a raw TimelinePostResource payload (GET /api/mobile/timeline on
 * heartdevs.com) into the flat view-model shape the timeline-post partial
 * expects. Shared by Timeline (the feed) and Home (the featured post +
 * latest-posts teaser) — both render the same API response.
 */
final class TimelinePostMapper
{
    /**
     * @param  array<string, mixed>  $post
     * @return array{id: string, author: string, avatarUrl: ?string, timeAgo: string, content: string, image: ?string, repliesCount: int, likesCount: int, likedByMe: bool}
     */
    public static function map(array $post): array
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
