<native:column class="w-full h-full items-center justify-center gap-4 p-6">
    @if ($status === 'loading')
        <native:activity-indicator />
        <native:text class="text-lg text-zinc-500">Chamando a API do He4rt...</native:text>
    @elseif ($status === 'ok')
        <native:text class="text-2xl font-extrabold text-zinc-900 text-center">Pong, {{ $username }}!</native:text>
        <native:text class="text-sm text-zinc-500 text-center">/api/mobile/me respondeu via Sanctum</native:text>
    @else
        <native:text class="text-2xl font-extrabold text-red-600 text-center">Falha ao chamar a API</native:text>
    @endif
</native:column>
