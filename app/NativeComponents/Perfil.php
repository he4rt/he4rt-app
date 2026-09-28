<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Perfil extends NativeComponent
{
    public string $apiStatus = 'loading';

    public ?string $apiUsername = null;

    public function mount(): void
    {
        $response = Http::withToken((string) config('services.he4rt_api.poc_token'))
            ->get(config('services.he4rt_api.base_url').'/api/mobile/me');

        if ($response->successful()) {
            $this->apiStatus = 'ok';
            $this->apiUsername = $response->json('username');

            return;
        }

        $this->apiStatus = 'error';
    }

    public function navTitle(): string
    {
        return 'Perfil';
    }

    public function render(): View
    {
        return view('native.perfil');
    }
}
