<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Support\AuthTokenStore;
use Illuminate\View\View;
use Native\Mobile\Attributes\Poll;
use Native\Mobile\Edge\NativeComponent;

class Splash extends NativeComponent
{
    private const int LAP_MS = 2_600;

    // Múltiplo exato da volta, pra não cortar a animação no meio.
    private const int DISPLAY_MS = self::LAP_MS * 2;

    #[Poll(self::DISPLAY_MS)]
    public function finish(): void
    {
        // Só checa se existe um token salvo — validade de verdade é
        // responsabilidade de quem chama a API (ver Perfil::loadProfile()),
        // que tenta refresh e só então desloga.
        $this->replace((new AuthTokenStore)->hasToken() ? '/home' : '/login');
    }

    public function navTitle(): string
    {
        return '';
    }

    public function render(): View
    {
        return view('native.splash');
    }
}
