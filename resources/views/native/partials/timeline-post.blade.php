@use('App\Icons\Ios')
@use('App\Icons\Android')

{{--
    Post real da timeline (GET /api/mobile/timeline no heartdevs.com).
    Espera $post: author, avatarUrl (nullable), timeAgo, content,
    image (nullable, primeira imagem do post), repliesCount, reactionsCount.
    Sem badge de cargo/tags/snippet de código — a API não tem esses campos
    (ao contrário do post mockado da Home, que é só decorativo).
--}}
<column class="w-full gap-3 bg-theme-surface border border-theme-outline-variant/60 rounded-[20] p-4">
    <row class="items-center gap-3">
        @if ($post['avatarUrl'])
            <image src="{{ $post['avatarUrl'] }}" alt="" class="w-10 h-10 rounded-full object-cover" />
        @else
            <column class="w-10 h-10 items-center justify-center bg-theme-primary/15 rounded-full">
                <icon name="person.fill" :ios="Ios::PersonFill" :android="Android::AccountCircle" size="18" class="text-theme-primary" />
            </column>
        @endif

        <column class="flex-1 gap-0.5">
            <text class="text-theme-on-surface font-bold">{{ $post['author'] }}</text>
            <text class="text-xs text-theme-on-surface-variant">{{ $post['timeAgo'] }}</text>
        </column>
    </row>

    <text class="text-sm text-theme-on-surface">{{ $post['content'] }}</text>

    @if ($post['image'])
        <image src="{{ $post['image'] }}" alt="" class="w-full h-[180] rounded-[12] object-cover" />
    @endif

    <row class="items-center gap-4 mt-1">
        <row class="items-center gap-1.5">
            <icon name="heart" :ios="Ios::Heart" :android="Android::FavoriteBorder" size="15" class="text-theme-on-surface-variant" />
            <text class="text-xs text-theme-on-surface-variant">{{ $post['reactionsCount'] }}</text>
        </row>
        <row class="items-center gap-1.5">
            <icon name="bubble.left" :ios="Ios::BubbleLeft" :android="Android::ChatBubbleOutline" size="15" class="text-theme-on-surface-variant" />
            <text class="text-xs text-theme-on-surface-variant">{{ $post['repliesCount'] }}</text>
        </row>
    </row>
</column>
