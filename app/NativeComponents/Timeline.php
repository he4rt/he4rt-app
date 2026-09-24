<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Timeline extends NativeComponent
{
    public function navTitle(): string
    {
        return 'Timeline';
    }

    public function render(): View
    {
        return view('native.timeline');
    }
}
