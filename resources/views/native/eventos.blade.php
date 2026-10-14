@use('App\Icons\Ios')
@use('App\Icons\Android')

<refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
    <column class="w-full gap-6 p-4 pb-10">
        @if ($status === 'loading')
            <column class="w-full items-center gap-3 p-8">
                <activity-indicator />
                <text class="text-sm text-theme-on-surface-variant">Carregando eventos...</text>
            </column>
        @elseif ($status === 'error')
            <column class="w-full items-center gap-3 p-8">
                <icon name="xmark.circle.fill" :ios="Ios::XmarkCircleFill" :android="Android::Cancel" size="28" class="text-theme-destructive" />
                <text class="text-sm text-theme-destructive text-center">Não foi possível carregar os eventos. Puxe pra baixo pra tentar de novo.</text>
            </column>
        @elseif (empty($upcomingEvents) && empty($pastEvents))
            <column class="w-full items-center gap-2 p-8">
                <icon name="calendar" :ios="Ios::Calendar" :android="Android::Event" size="28" class="text-theme-on-surface-variant" />
                <text class="text-sm text-theme-on-surface-variant text-center">Nenhum evento no momento.</text>
            </column>
        @else
            @if (!empty($upcomingEvents))
                <column class="w-full gap-3">
                    <text class="text-lg text-theme-on-background font-bold">Próximos</text>
                    <column class="w-full gap-2">
                        @foreach ($upcomingEvents as $event)
                            @include('native.partials.event-card', ['event' => $event])
                        @endforeach
                    </column>
                </column>
            @endif

            @if (!empty($pastEvents))
                <column class="w-full gap-3">
                    <text class="text-lg text-theme-on-background font-bold">Anteriores</text>
                    <column class="w-full gap-2">
                        @foreach ($pastEvents as $event)
                            @include('native.partials.event-card', ['event' => $event])
                        @endforeach
                    </column>
                </column>
            @endif
        @endif
    </column>
</refreshable>
