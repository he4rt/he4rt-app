@use('App\Icons\Ios')
@use('App\Icons\Android')

<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-6 p-4 pb-10">

        {{-- Hero: card de boas-vindas com gradiente da marca, não a réplica do terminal do site --}}
        <column class="w-full gap-4 p-5 bg-gradient-to-br from-theme-primary to-theme-secondary rounded-[24]">
            <row class="w-full items-center justify-between">
                <column class="gap-1">
                    <text class="text-sm text-white/80">Bem-vindo de volta</text>
                    <text class="text-xl text-white font-extrabold">Comunidade He4rt</text>
                </column>
                <column class="w-11 h-11 items-center justify-center bg-white/15 rounded-full">
                    <icon name="bolt.fill" :ios="Ios::BoltFill" :android="Android::Bolt" size="18" class="text-white" />
                </column>
            </row>

            <row class="w-full gap-3">
                <column class="flex-1 gap-1 bg-white/12 rounded-[14] p-3">
                    <text class="text-xs text-white/70 uppercase tracking-wide">Membros</text>
                    <text class="text-lg text-white font-extrabold" font="mono">{{ $stats['membros'] }}</text>
                </column>
                <column class="flex-1 gap-1 bg-white/12 rounded-[14] p-3">
                    <text class="text-xs text-white/70 uppercase tracking-wide">Online agora</text>
                    <text class="text-lg text-white font-extrabold" font="mono">{{ $stats['online'] }}</text>
                </column>
                <column class="flex-1 gap-1 bg-white/12 rounded-[14] p-3">
                    <text class="text-xs text-white/70 uppercase tracking-wide">Mensagens</text>
                    <text class="text-lg text-white font-extrabold" font="mono">{{ $stats['mensagens'] }}</text>
                </column>
            </row>

            <pressable @press="goToDiscord" class="w-full">
                <row class="w-full items-center justify-between bg-white/15 rounded-full px-4 py-3">
                    <row class="items-center gap-2.5">
                        <icon name="bubble.left.and.bubble.right.fill" :ios="Ios::BubbleLeftAndBubbleRightFill" :android="Android::Forum" size="15" class="text-white" />
                        <text class="text-sm text-white" font="medium">Acessar Discord da He4rt</text>
                    </row>
                    <icon name="chevron.right" :ios="Ios::ChevronRight" :android="Android::ChevronRight" size="15" class="text-white" />
                </row>
            </pressable>
        </column>

        {{-- Próximos eventos --}}
        <column class="w-full gap-3">
            <row class="w-full items-center justify-between">
                <row class="items-center gap-2">
                    <icon name="calendar" :ios="Ios::Calendar" :android="Android::Event" size="18" class="text-theme-on-background" />
                    <text class="text-lg text-theme-on-background font-bold">Próximos eventos</text>
                </row>
                <pressable @press="goToEventos">
                    <text class="text-sm text-theme-primary" font="medium">Ver todos</text>
                </pressable>
            </row>

            <carousel variant="uncontained" item-width="240" item-spacing="12" class="w-full">
                @foreach ($upcomingEvents as $event)
                    <pressable @press="goToEventos">
                        <column class="w-[240] h-[172] justify-between bg-theme-surface border border-theme-outline-variant/60 rounded-[20] p-4">
                            <column class="gap-3">
                                <row class="items-center gap-1.5 bg-theme-{{ $event['statusToken'] }}/15 rounded-full px-2.5 py-1 self-start">
                                    <circle class="w-1.5 h-1.5 bg-theme-{{ $event['statusToken'] }}" />
                                    <text class="text-xs text-theme-{{ $event['statusToken'] }} font-semibold">{{ $event['status'] }}</text>
                                </row>

                                <text class="text-base text-theme-on-surface font-bold" max-lines="2">{{ $event['title'] }}</text>
                            </column>

                            <row class="items-center gap-1.5">
                                <icon name="calendar" :ios="Ios::Calendar" :android="Android::Event" size="13" class="text-theme-on-surface-variant" />
                                <text class="text-xs text-theme-on-surface-variant">{{ $event['when'] }}</text>
                            </row>
                        </column>
                    </pressable>
                @endforeach
            </carousel>
        </column>

        {{-- Timeline / comunidade --}}
        <column class="w-full gap-3">
            <row class="w-full items-center justify-between">
                <row class="items-center gap-2">
                    <icon name="bubble.left.fill" :ios="Ios::BubbleLeftFill" :android="Android::Forum" size="18" class="text-theme-on-background" />
                    <text class="text-lg text-theme-on-background font-bold">Da comunidade</text>
                </row>
                <pressable @press="goToTimeline">
                    <text class="text-sm text-theme-primary" font="medium">Ver timeline</text>
                </pressable>
            </row>

            {{-- Post em destaque — mesmo card reutilizável da Timeline --}}
            @include('native.partials.post-card', ['post' => $featuredPost])

            <column class="w-full gap-2">
                @foreach ($latestPosts as $post)
                    <list-item
                        headline="{{ $post['title'] }}"
                        supporting="{{ $post['author'] }} · {{ $post['timeAgo'] }}"
                        leadingIcon="bubble.left.fill"
                        :leadingIconIos="Ios::BubbleLeftFill"
                        :leadingIconAndroid="Android::Forum"
                        class="w-full bg-theme-surface border border-theme-outline-variant/40 rounded-[16] px-3"
                    />
                @endforeach
            </column>
        </column>

        <button variant="primary" class="w-full mt-2" @press="goToTimeline">Ver timeline completa</button>
    </column>
</scroll-view>
