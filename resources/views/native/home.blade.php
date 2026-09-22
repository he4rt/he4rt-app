<column class="w-full h-full items-center justify-center gap-4 p-6 bg-theme-background safe-area">
    @if ($status === 'loading')
        <activity-indicator />
        <text class="text-lg text-theme-on-surface-variant">Chamando a API do He4rt...</text>
    @elseif ($status === 'ok')
        <text class="text-2xl font-extrabold text-theme-on-surface text-center">Pong, {{ $username }}!</text>
        <text class="text-sm text-theme-on-surface-variant text-center">/api/mobile/me respondeu via Sanctum</text>
    @else
        <text class="text-2xl font-extrabold text-theme-destructive text-center">Falha ao chamar a API</text>
    @endif
</column>
