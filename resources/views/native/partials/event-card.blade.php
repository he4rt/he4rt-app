@use('App\Icons\Ios')
@use('App\Icons\Android')

{{--
    Card de evento real (GET /api/mobile/events no heartdevs.com).
    Espera $event: id, title, typeLabel, when, location (nullable),
    statusLabel (nullable — null quando o usuário não tem inscrição),
    statusToken.
--}}
<pressable @press="goToEvent('{{ $event['id'] }}')" class="w-full">
    <column class="w-full gap-2 bg-theme-surface border border-theme-outline-variant/60 rounded-[18] p-4">
        <row class="w-full items-center justify-between">
            <row class="items-center gap-1.5 bg-theme-surface-variant rounded-full px-2.5 py-1">
                <text class="text-xs text-theme-on-surface-variant font-semibold">{{ $event['typeLabel'] }}</text>
            </row>
            @if ($event['statusLabel'])
                <row class="items-center gap-1.5 bg-theme-{{ $event['statusToken'] }}/15 rounded-full px-2.5 py-1">
                    <circle class="w-1.5 h-1.5 bg-theme-{{ $event['statusToken'] }}" />
                    <text class="text-xs text-theme-{{ $event['statusToken'] }} font-semibold">{{ $event['statusLabel'] }}</text>
                </row>
            @endif
        </row>

        <text class="text-base text-theme-on-surface font-bold" max-lines="2">{{ $event['title'] }}</text>

        <row class="items-center gap-1.5">
            <icon name="calendar" :ios="Ios::Calendar" :android="Android::Event" size="13" class="text-theme-on-surface-variant" />
            <text class="text-xs text-theme-on-surface-variant">{{ $event['when'] }}</text>
        </row>

        @if ($event['location'])
            <row class="items-center gap-1.5">
                <icon name="mappin" :ios="Ios::Mappin" :android="Android::Place" size="13" class="text-theme-on-surface-variant" />
                <text class="text-xs text-theme-on-surface-variant">{{ $event['location'] }}</text>
            </row>
        @endif
    </column>
</pressable>
