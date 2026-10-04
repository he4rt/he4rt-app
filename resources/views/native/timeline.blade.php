@use('App\Icons\Ios')
@use('App\Icons\Android')

<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-4 p-4 pb-10">
        <scroll-view horizontal class="w-full">
            <row class="gap-2">
                <chip label="Tudo" :selected="$activeFilter === 'todos'" @change="setFilter('todos')" />
                <chip label="Projetos" :selected="$activeFilter === 'projetos'" @change="setFilter('projetos')" />
                <chip label="Eventos" :selected="$activeFilter === 'eventos'" @change="setFilter('eventos')" />
                <chip label="Mentorias" :selected="$activeFilter === 'mentorias'" @change="setFilter('mentorias')" />
                <chip label="Geral" :selected="$activeFilter === 'geral'" @change="setFilter('geral')" />
            </row>
        </scroll-view>

        <column class="w-full gap-3">
            @forelse ($posts as $post)
                @include('native.partials.post-card', ['post' => $post])
            @empty
                <column class="w-full items-center gap-2 p-8">
                    <icon name="bubble.left.and.bubble.right" :ios="Ios::BubbleLeftAndBubbleRight" :android="Android::ChatBubbleOutline" size="28" class="text-theme-on-surface-variant" />
                    <text class="text-sm text-theme-on-surface-variant text-center">Nada por aqui ainda nessa categoria.</text>
                </column>
            @endforelse
        </column>
    </column>
</scroll-view>
