@use('App\Icons\Ios')
@use('App\Icons\Android')

<refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
    <column class="w-full gap-3 p-4 pb-10">
        @if ($status === 'loading')
            <column class="w-full items-center gap-3 p-8">
                <activity-indicator />
                <text class="text-sm text-theme-on-surface-variant">Carregando timeline...</text>
            </column>
        @elseif ($status === 'error')
            <column class="w-full items-center gap-3 p-8">
                <icon name="xmark.circle.fill" :ios="Ios::XmarkCircleFill" :android="Android::Cancel" size="28" class="text-theme-destructive" />
                <text class="text-sm text-theme-destructive text-center">Não foi possível carregar a timeline. Puxe pra baixo pra tentar de novo.</text>
            </column>
        @elseif (empty($posts))
            <column class="w-full items-center gap-2 p-8">
                <icon name="bubble.left.and.bubble.right" :ios="Ios::BubbleLeftAndBubbleRight" :android="Android::ChatBubbleOutline" size="28" class="text-theme-on-surface-variant" />
                <text class="text-sm text-theme-on-surface-variant text-center">Ainda não tem nada na timeline. Seja o primeiro a postar!</text>
            </column>
        @else
            @foreach ($posts as $post)
                @include('native.partials.timeline-post', ['post' => $post])
            @endforeach
        @endif
    </column>
</refreshable>
