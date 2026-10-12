@use('App\Icons\Ios')
@use('App\Icons\Android')

<column class="w-full h-full">
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

                @if ($nextPageUrl)
                    <pressable @press="loadMore" class="w-full items-center p-3">
                        @if ($isLoadingMore)
                            <activity-indicator />
                        @else
                            <text class="text-sm text-theme-primary" font="medium">Carregar mais</text>
                        @endif
                    </pressable>
                @endif
            @endif
        </column>
    </refreshable>

    {{--
        Composer de resposta. Não existe endpoint pra listar respostas já
        criadas (só POST/DELETE) — enviar só soma +1 no repliesCount
        visível, não aparece como thread em nenhum lugar.
    --}}
    <bottom-sheet :visible="$replyingToPostId !== null" @dismiss="cancelReply">
        <column class="w-full gap-3 p-4">
            <text class="text-lg text-theme-on-surface font-bold">Responder</text>

            <filled-text-input
                native:model="replyContent"
                placeholder="Escreva sua resposta..."
                multiline
                min-lines="3"
                max-lines="6"
            />

            @if ($replyError)
                <text class="text-sm text-theme-destructive">{{ $replyError }}</text>
            @endif

            <button variant="primary" :loading="$isSubmittingReply" @press="submitReply">Enviar</button>
        </column>
    </bottom-sheet>
</column>
