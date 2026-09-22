<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Attributes\Poll;
use Native\Mobile\Edge\NativeComponent;

class Splash extends NativeComponent
{
    private const int DISPLAY_MS = 1_800;

    #[Poll(self::DISPLAY_MS)]
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
