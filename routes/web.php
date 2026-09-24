<?php

declare(strict_types=1);

use App\NativeComponents\Home;
use App\NativeComponents\Splash;
use Illuminate\Support\Facades\Route;

Route::native('/', Splash::class);
Route::native('/home', Home::class);

// Servida pro <native:webview php> da Splash, não é uma tela nativa.
Route::view('/web/splash-animation', 'web.splash-animation');
