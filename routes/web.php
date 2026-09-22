<?php

declare(strict_types=1);

use App\NativeComponents\Home;
use Illuminate\Support\Facades\Route;

Route::native('/', Home::class);
