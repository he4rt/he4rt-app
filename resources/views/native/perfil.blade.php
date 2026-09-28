@use('App\Icons\Ios')
@use('App\Icons\Android')

<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-6 p-4">

        <row class="w-full items-center gap-4">
            <column class="w-16 h-16 items-center justify-center bg-theme-primary/15 rounded-full">
                <icon name="person.fill" :ios="Ios::PersonFill" :android="Android::AccountCircle" size="30" class="text-theme-primary" />
            </column>

            <column class="gap-0.5">
                <text class="text-xl text-theme-on-surface font-bold">Convidado</text>
                <text class="text-sm text-theme-on-surface-variant">Login com a comunidade chegando em breve</text>
            </column>
        </row>

        <column class="w-full gap-3">
            <text class="text-lg text-theme-on-surface font-bold">Conexão com a API</text>

            <row class="w-full items-center gap-3 bg-theme-surface rounded-[16] p-4">
                @if ($apiStatus === 'loading')
                    <activity-indicator />
                    <column class="gap-0.5">
                        <text class="text-theme-on-surface" font="medium">Conectando...</text>
                        <text class="text-xs text-theme-on-surface-variant" font="mono">GET /api/mobile/me</text>
                    </column>
                @elseif ($apiStatus === 'ok')
                    <icon name="checkmark.circle.fill" :ios="Ios::CheckmarkCircleFill" :android="Android::CheckCircle" size="24" class="text-theme-success" />
                    <column class="gap-0.5">
                        <text class="text-theme-on-surface" font="medium">Conectado como {{ $apiUsername }}</text>
                        <text class="text-xs text-theme-on-surface-variant" font="mono">GET /api/mobile/me · Sanctum (POC)</text>
                    </column>
                @else
                    <icon name="xmark.circle.fill" :ios="Ios::XmarkCircleFill" :android="Android::Cancel" size="24" class="text-theme-destructive" />
                    <column class="gap-0.5">
                        <text class="text-theme-on-surface" font="medium">Falha ao conectar com a API</text>
                        <text class="text-xs text-theme-on-surface-variant" font="mono">GET /api/mobile/me · Sanctum (POC)</text>
                    </column>
                @endif
            </row>
        </column>
    </column>
</scroll-view>
