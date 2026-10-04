@use('App\Icons\Ios')
@use('App\Icons\Android')

{{--
    Card de post reutilizável da Timeline. Espera $post com as chaves:
    author, role (nullable), timeAgo, body, code (nullable: file/lang/lines),
    tags (array), likes, comments.
--}}
<column class="w-full gap-3 bg-theme-surface border border-theme-outline-variant/60 rounded-[20] p-4">
    <row class="items-center gap-3">
        <column class="w-10 h-10 items-center justify-center bg-theme-primary/15 rounded-full">
            <icon name="person.fill" :ios="Ios::PersonFill" :android="Android::AccountCircle" size="18" class="text-theme-primary" />
        </column>
        <column class="flex-1 gap-0.5">
            <row class="items-center gap-2">
                <text class="text-theme-on-surface font-bold">{{ $post['author'] }}</text>
                @if (! empty($post['role']))
                    <row class="items-center bg-theme-accent/15 rounded-full px-2 py-0.5">
                        <text class="text-xs text-theme-accent font-bold">{{ $post['role'] }}</text>
                    </row>
                @endif
            </row>
            <text class="text-xs text-theme-on-surface-variant">{{ $post['timeAgo'] }}</text>
        </column>
    </row>

    <text class="text-sm text-theme-on-surface">{{ $post['body'] }}</text>

    @if (! empty($post['code']))
        <column class="w-full gap-1 bg-theme-background border border-theme-outline-variant/40 rounded-[12] p-3">
            <row class="w-full items-center justify-between">
                <text class="text-xs text-theme-on-surface-variant" font="mono">{{ $post['code']['file'] }}</text>
                <text class="text-xs text-theme-accent uppercase tracking-wide" font="mono">{{ $post['code']['lang'] }}</text>
            </row>
            @foreach ($post['code']['lines'] as $line)
                <text class="text-xs text-theme-on-surface-variant" font="mono">{{ $line }}</text>
            @endforeach
        </column>
    @endif

    @if (! empty($post['tags']))
        <row class="gap-2">
            @foreach ($post['tags'] as $tag)
                <row class="bg-theme-surface-variant rounded-full px-2.5 py-1">
                    <text class="text-xs text-theme-on-surface-variant">{{ $tag }}</text>
                </row>
            @endforeach
        </row>
    @endif

    <row class="items-center gap-4 mt-1">
        <row class="items-center gap-1.5">
            <icon name="heart" :ios="Ios::Heart" :android="Android::FavoriteBorder" size="15" class="text-theme-on-surface-variant" />
            <text class="text-xs text-theme-on-surface-variant">{{ $post['likes'] }}</text>
        </row>
        <row class="items-center gap-1.5">
            <icon name="bubble.left" :ios="Ios::BubbleLeft" :android="Android::ChatBubbleOutline" size="15" class="text-theme-on-surface-variant" />
            <text class="text-xs text-theme-on-surface-variant">{{ $post['comments'] }}</text>
        </row>
        <icon name="bookmark" :ios="Ios::Bookmark" :android="Android::BookmarkBorder" size="15" class="text-theme-on-surface-variant" />
        <icon name="square.and.arrow.up" :ios="Ios::SquareAndArrowUp" :android="Android::Share" size="15" class="text-theme-on-surface-variant" />
    </row>
</column>
