<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Eventos extends NativeComponent
{
    public function navTitle(): string
    {
        return 'Eventos';
    }

    public function render(): View
    {
        return view('native.eventos');
    }
}
