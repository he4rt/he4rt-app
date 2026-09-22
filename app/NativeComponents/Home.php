<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Home extends NativeComponent
{
    public string $status = 'loading';

    public ?string $username = null;

    public function mount(): void
    {
        $response = Http::withToken((string) config('services.he4rt_api.poc_token'))
            ->get(config('services.he4rt_api.base_url').'/api/mobile/me');

        if ($response->successful()) {
            $this->status = 'ok';
            $this->username = $response->json('username');

            return;
        }

        $this->status = 'error';
    }

    public function navTitle(): string
    {
        return 'He4rt';
    }

    public function render(): View
    {
        return view('native.home');
    }
}
