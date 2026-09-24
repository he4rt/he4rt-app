<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Attributes\Poll;
use Native\Mobile\Edge\NativeComponent;

/**
 * O EDGE não desenha paths/stroke-dasharray arbitrários, então o logo
 * animado do portal (app-modules/portal/resources/views/components/
 * animated-logo.blade.php, heartdevs.com) não tem equivalente nativo direto.
 * Em vez de aproximar, essa tela embute o HTML/CSS/SVG reais dele — ver
 * resources/views/web/splash-animation.blade.php — via <native:webview php>,
 * a única forma de ter fidelidade 1:1 com a animação original.
 */
class Splash extends NativeComponent
{
    private const int LAP_MS = 2_600;

    #[Poll(self::LAP_MS)]
    public function finish(): void
    {
        $this->replace('/home');
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
