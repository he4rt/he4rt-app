<?php

declare(strict_types=1);

use App\NativeComponents\Eventos;
use App\NativeComponents\Home;
use App\NativeComponents\Perfil;
use App\NativeComponents\Splash;
use App\NativeComponents\Timeline;
use App\NativeLayouts\TabsLayout;
use Illuminate\Support\Facades\Route;

Route::native('/', Splash::class);

Route::nativeGroup(layout: TabsLayout::class, routes: function (): void {
    Route::native('/home', Home::class);
    Route::native('/timeline', Timeline::class);
    Route::native('/eventos', Eventos::class);
    Route::native('/perfil', Perfil::class);
});

// Servida pro <native:webview php> da Splash, não é uma tela nativa.
Route::view('/web/splash-animation', 'web.splash-animation');
