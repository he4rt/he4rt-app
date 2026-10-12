@use('App\Icons\Ios')
@use('App\Icons\Android')

<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-6 p-4 pb-10">

        @if ($status === 'loading')
            <column class="w-full items-center gap-3 p-8">
                <activity-indicator />
                <text class="text-sm text-theme-on-surface-variant">Carregando perfil...</text>
            </column>
        @elseif ($status === 'ok')
            {{-- Identidade --}}
            <row class="w-full items-center gap-4">
                @if ($avatarUrl)
                    <image src="{{ $avatarUrl }}" alt="Avatar de {{ $username }}" class="w-16 h-16 rounded-full object-cover" />
                @else
                    <column class="w-16 h-16 items-center justify-center bg-theme-primary/15 rounded-full">
                        <icon name="person.fill" :ios="Ios::PersonFill" :android="Android::AccountCircle" size="30" class="text-theme-primary" />
                    </column>
                @endif

                <column class="flex-1 gap-0.5">
                    <text class="text-xl text-theme-on-background font-bold">{{ $username }}</text>
                    @if ($headline)
                        <text class="text-sm text-theme-on-surface-variant">{{ $headline }}</text>
                    @else
                        <text class="text-sm text-theme-on-surface-variant">Membro da comunidade He4rt</text>
                    @endif
                </column>
            </row>

            {{-- Senioridade / experiência --}}
            @if ($seniorityLabel || $yearsExperience)
                <row class="gap-2">
                    @if ($seniorityLabel)
                        <row class="items-center gap-1.5 bg-theme-primary/15 rounded-full px-3 py-1.5">
                            <icon name="star.fill" :ios="Ios::StarFill" :android="Android::Star" size="13" class="text-theme-primary" />
                            <text class="text-xs text-theme-primary font-semibold">{{ $seniorityLabel }}</text>
                        </row>
                    @endif
                    @if ($yearsExperience)
                        <row class="items-center gap-1.5 bg-theme-surface-variant rounded-full px-3 py-1.5">
                            <icon name="briefcase.fill" :ios="Ios::BriefcaseFill" :android="Android::Work" size="13" class="text-theme-on-surface-variant" />
                            <text class="text-xs text-theme-on-surface-variant font-semibold">{{ $yearsExperience }} {{ $yearsExperience === 1 ? 'ano' : 'anos' }} de experiência</text>
                        </row>
                    @endif
                </row>
            @endif

            {{-- Disponibilidade --}}
            @if ($availableForProposals)
                <row class="items-center gap-2 bg-theme-success/15 rounded-[14] p-3">
                    <icon name="checkmark.circle.fill" :ios="Ios::CheckmarkCircleFill" :android="Android::CheckCircle" size="16" class="text-theme-success" />
                    <text class="text-sm text-theme-success" font="medium">
                        Aberto a propostas
                        @if ($startAvailabilityLabel)
                            · disponível em {{ $startAvailabilityLabel }}
                        @endif
                    </text>
                </row>
            @endif

            {{-- Sobre --}}
            @if ($about)
                <column class="w-full gap-2">
                    <text class="text-lg text-theme-on-background font-bold">Sobre</text>
                    <text class="text-sm text-theme-on-surface">{{ $about }}</text>
                </column>
            @endif

            {{-- Skills --}}
            @if (!empty($skills))
                <column class="w-full gap-2">
                    <text class="text-lg text-theme-on-background font-bold">Skills</text>
                    <row class="flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <chip label="{{ $skill }}" selected class="shrink-0" />
                        @endforeach
                    </row>
                </column>
            @endif

            {{-- Experiência profissional --}}
            @if (!empty($workExperiences))
                <column class="w-full gap-2">
                    <text class="text-lg text-theme-on-background font-bold">Experiência</text>
                    <column class="w-full gap-2">
                        @foreach ($workExperiences as $experience)
                            <column class="w-full gap-1 bg-theme-surface border border-theme-outline-variant/60 rounded-[16] p-3">
                                <text class="text-sm text-theme-on-surface font-bold">{{ $experience['position'] }}</text>
                                <text class="text-sm text-theme-on-surface-variant">{{ $experience['company'] }}</text>
                                <row class="items-center gap-1.5 mt-1">
                                    <icon name="calendar" :ios="Ios::Calendar" :android="Android::Event" size="12" class="text-theme-on-surface-variant" />
                                    <text class="text-xs text-theme-on-surface-variant">{{ $experience['period'] }}</text>
                                </row>
                                @if ($experience['description'])
                                    <text class="text-xs text-theme-on-surface-variant mt-1">{{ $experience['description'] }}</text>
                                @endif
                            </column>
                        @endforeach
                    </column>
                </column>
            @endif

            {{-- Links sociais --}}
            @if (!empty($socialLinks))
                <column class="w-full gap-2">
                    <text class="text-lg text-theme-on-background font-bold">Links</text>
                    <row class="flex-wrap gap-2">
                        @foreach ($socialLinks as $link)
                            <pressable @press="openSocialLink('{{ $link['url'] }}')">
                                <row class="items-center gap-1.5 bg-theme-surface-variant rounded-full px-3 py-1.5">
                                    <icon name="link" :ios="Ios::Link" :android="Android::Link" size="13" class="text-theme-on-surface-variant" />
                                    <text class="text-xs text-theme-on-surface-variant font-medium">{{ $link['label'] }}</text>
                                </row>
                            </pressable>
                        @endforeach
                    </row>
                </column>
            @endif

            <pressable @press="logout" class="w-full mt-2">
                <row class="w-full items-center justify-center gap-2 bg-theme-destructive/15 rounded-[14] p-3">
                    <icon name="rectangle.portrait.and.arrow.right" :ios="Ios::RectanglePortraitAndArrowRight" :android="Android::Logout" size="16" class="text-theme-destructive" />
                    <text class="text-sm text-theme-destructive" font="medium">Sair</text>
                </row>
            </pressable>
        @else
            <column class="w-full items-center gap-3 p-8">
                <icon name="xmark.circle.fill" :ios="Ios::XmarkCircleFill" :android="Android::Cancel" size="28" class="text-theme-destructive" />
                <text class="text-sm text-theme-destructive text-center">Não foi possível carregar seu perfil. Tente novamente mais tarde.</text>
            </column>
        @endif
    </column>
</scroll-view>
