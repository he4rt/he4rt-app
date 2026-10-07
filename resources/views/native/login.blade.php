@use('App\Icons\Ios')
@use('App\Icons\Android')

<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-8 p-6 pt-16 pb-10 items-center">

        <column class="items-center gap-4">
            <column class="w-16 h-16 items-center justify-center bg-theme-primary/15 rounded-[16]">
                <icon name="curlybraces" :ios="Ios::Curlybraces" :android="Android::Code" size="28" class="text-theme-primary" />
            </column>

            <column class="items-center gap-1">
                <text class="text-xl text-theme-on-background font-extrabold text-center">He4rt Developers</text>
                <text class="text-sm text-theme-on-surface-variant text-center">Comunidade brasileira de desenvolvedores. Aprenda, compartilhe e cresça.</text>
            </column>

            <row class="items-center gap-4">
                <column class="items-center gap-0.5">
                    <text class="text-base text-theme-on-background font-bold">5k+</text>
                    <text class="text-xs text-theme-on-surface-variant">membros</text>
                </column>
                <column class="items-center gap-0.5">
                    <text class="text-base text-theme-on-background font-bold">200+</text>
                    <text class="text-xs text-theme-on-surface-variant">eventos</text>
                </column>
                <column class="items-center gap-0.5">
                    <text class="text-base text-theme-on-background font-bold">50+</text>
                    <text class="text-xs text-theme-on-surface-variant">projetos</text>
                </column>
            </row>
        </column>

        <column class="w-full gap-4">
            <column class="w-full gap-1">
                <text class="text-lg text-theme-on-background font-bold">Entrar</text>
                <text class="text-sm text-theme-on-surface-variant">Acesse sua conta He4rt Developers</text>
            </column>

            @if ($error)
                <row class="w-full items-center gap-2 bg-theme-destructive/15 rounded-[12] p-3">
                    <icon name="exclamationmark.triangle.fill" :ios="Ios::ExclamationmarkTriangleFill" :android="Android::Warning" size="15" class="text-theme-destructive" />
                    <text class="flex-1 text-sm text-theme-destructive">{{ $error }}</text>
                </row>
            @endif

            <column class="w-full gap-3">
                <pressable @press="loginWithDiscord" class="w-full">
                    <row class="w-full items-center gap-3 bg-[#5865F2] rounded-[14] px-4 py-3">
                        <icon name="gamecontroller.fill" :ios="Ios::GamecontrollerFill" :android="Android::VideogameAsset" size="18" class="text-white" />
                        <text class="text-sm text-white" font="medium">Continuar com Discord</text>
                    </row>
                </pressable>

                <pressable @press="loginWithGitHub" class="w-full">
                    <row class="w-full items-center gap-3 bg-[#18181B] rounded-[14] px-4 py-3">
                        <icon name="curlybraces" :ios="Ios::Curlybraces" :android="Android::Code" size="18" class="text-white" />
                        <text class="text-sm text-white" font="medium">Continuar com GitHub</text>
                    </row>
                </pressable>

                <pressable @press="loginWithTwitch" class="w-full">
                    <row class="w-full items-center gap-3 bg-[#9146FF] rounded-[14] px-4 py-3">
                        <icon name="play.tv.fill" :ios="Ios::PlayTvFill" :android="Android::LiveTv" size="18" class="text-white" />
                        <text class="text-sm text-white" font="medium">Continuar com Twitch</text>
                    </row>
                </pressable>
            </column>

            <text class="text-xs text-theme-on-surface-variant text-center mt-1">Login com e-mail e senha chega em breve.</text>
        </column>
    </column>
</scroll-view>
