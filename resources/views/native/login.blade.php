@use('App\Icons\Ios')
@use('App\Icons\Android')

<column class="w-full h-full bg-theme-background items-center justify-center gap-10 p-6 safe-area">

    <column class="items-center gap-3">
        <column class="w-20 h-20 items-center justify-center bg-theme-primary/15 rounded-[20]">
            <image src="{{ App\Support\LocalAsset::url('images/heart-logo.png') }}" alt="He4rt Developers" class="w-[37] h-[32] object-contain" />
        </column>
        <text class="text-xl text-theme-on-background font-extrabold text-center">He4rt Developers</text>
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

        {{-- public/images/{discord,github,twitch}.png: rasterizados do Font Awesome Brands (CC BY 4.0) usado no heartdevs.com — EDGE só decodifica raster, não SVG. --}}
        <column class="w-full gap-3">
            <pressable @press="loginWithDiscord" class="w-full">
                <row class="w-full items-center gap-3 bg-[#5865F2] rounded-[14] px-4 py-3">
                    <image src="{{ App\Support\LocalAsset::url('images/discord.png') }}" alt="" class="w-[20] h-[18] object-contain" />
                    <text class="text-sm text-white" font="medium">Continuar com Discord</text>
                </row>
            </pressable>

            <pressable @press="loginWithGitHub" class="w-full">
                <row class="w-full items-center gap-3 bg-[#18181B] rounded-[14] px-4 py-3">
                    <image src="{{ App\Support\LocalAsset::url('images/github.png') }}" alt="" class="w-[18] h-[18] object-contain" />
                    <text class="text-sm text-white" font="medium">Continuar com GitHub</text>
                </row>
            </pressable>

            <pressable @press="loginWithTwitch" class="w-full">
                <row class="w-full items-center gap-3 bg-[#9146FF] rounded-[14] px-4 py-3">
                    <image src="{{ App\Support\LocalAsset::url('images/twitch.png') }}" alt="" class="w-[16] h-[18] object-contain" />
                    <text class="text-sm text-white" font="medium">Continuar com Twitch</text>
                </row>
            </pressable>
        </column>

        <text class="text-xs text-theme-on-surface-variant text-center mt-1">Login com e-mail e senha chega em breve.</text>
    </column>
</column>
