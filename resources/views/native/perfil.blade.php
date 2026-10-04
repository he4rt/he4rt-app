@use('App\Icons\Ios')
@use('App\Icons\Android')

<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-6 p-4">

        @if ($status === 'loading')
            <column class="w-full items-center gap-3 p-8">
                <activity-indicator />
                <text class="text-sm text-theme-on-surface-variant">Carregando perfil...</text>
            </column>
        @elseif ($status === 'ok')
            <row class="w-full items-center gap-4">
                @if ($avatarUrl)
                    <image src="{{ $avatarUrl }}" alt="Avatar de {{ $username }}" class="w-16 h-16 rounded-full object-cover" />
                @else
                    <column class="w-16 h-16 items-center justify-center bg-theme-primary/15 rounded-full">
                        <icon name="person.fill" :ios="Ios::PersonFill" :android="Android::AccountCircle" size="30" class="text-theme-primary" />
                    </column>
                @endif

                <column class="gap-0.5">
                    <text class="text-xl text-theme-on-background font-bold">{{ $username }}</text>
                    <text class="text-sm text-theme-on-surface-variant">Membro da comunidade He4rt</text>
                </column>
            </row>

            <pressable @press="logout" class="w-full">
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
