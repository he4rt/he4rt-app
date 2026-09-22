# He4rt App

App móvel oficial da comunidade He4rt (iOS/Android), construído com [NativePHP Mobile](https://nativephp.com/docs/mobile/4/getting-started/introduction)
(SuperNative) consumindo a API do [heartdevs.com](https://github.com/he4rt/heartdevs.com).

Escopo e decisões de produto: [he4rt/heartdevs.com#531](https://github.com/he4rt/heartdevs.com/issues/531).

## Arquitetura

- Telas nativas (SwiftUI/Jetpack Compose) escritas em EDGE Blade, sem WebView.
- Sem backend próprio: todo domínio (usuários, eventos, perfil) vive no `heartdevs.com`,
  consumido via API autenticada por Sanctum.
- Banco local (SQLite) é usado apenas para estado on-device, nunca como fonte de verdade.

## Setup local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Configure `HE4RT_API_BASE_URL` e `HE4RT_API_POC_TOKEN` no `.env` apontando pra uma instância
local do `heartdevs.com` (ver `config/services.php`).

## Rodando no simulador/emulador

```bash
php artisan native:run ios       # ou: android
php artisan native:watch         # hot reload
```

Veja o skill `nativephp-mobile` no `CLAUDE.md` pra convenções de componentes, EDGE e device APIs.
