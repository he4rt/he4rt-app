<?php

declare(strict_types=1);

/**
 * Native UI — Theme Tokens
 *
 * Published via `php artisan vendor:publish --tag=native-ui-config`.
 * Edit to customize your app's visual identity in one place.
 *
 * For dynamic per-tenant theming, use Native\Mobile\UI\Theme::merge([...])
 * from a service provider. Runtime merges deep-merge on top of these values.
 *
 * Valores portados de heartdevs.com/app-modules/he4rt/.../themes.css.
 */

return [

    /*
    |---------------------------------------------------------------------------
    | Theme
    |---------------------------------------------------------------------------
    |
    | Color tokens (open-ended map), 4 radii, 4 font sizes, font family.
    |
    | "on-X" means "color of content placed ON a surface of color X"
    |   — i.e., text/icons on that background.
    |
    | The token map is OPEN-ENDED: add any key your design needs (e.g. a
    | `warning` pair) to both blocks and `bg-theme-warning` /
    | `text-theme-on-warning` / `border-theme-warning` resolve immediately.
    | Theme classes also accept opacity modifiers — `bg-theme-primary/15`
    | is the tonal-fill idiom (the alpha applies to the dark companion
    | too). In PHP (layout chrome builders, dynamic styling) read tokens
    | with the appearance-aware `theme()` helper: `theme('primary')`.
    |
    | Color tokens accept:
    |   - CSS hex: '#B91C1C', '#F00', or with alpha '#8B5CF680' (#RRGGBBAA)
    |   - Tailwind palette names: 'red-300', 'orange-800'
    |   - Opacity modifiers on either: 'red-300/20', '#8B5CF6/50'
    |
    | Dark mode is auto-derived from `light` when `dark` is not set. To opt
    | into explicit dark tokens, fill out the `dark` block.
    |
    | The default pairs meet WCAG AA (4.5:1) — if you customize, keep each
    | `on-*` color at 4.5:1 contrast against its background token.
    |
    */

    'theme' => [

        'light' => [
            // Primary/secondary do He4rt são fixos entre os dois temas
            // (themes.css não os sobrescreve no bloco .dark).
            'primary' => '#782BF1',
            'on-primary' => '#FFFFFF',

            'secondary' => '#9828BD',
            'on-secondary' => '#FFFFFF',

            // Surface = elevation-01dp (cards/sheets). Background = elevation-surface (raiz).
            'surface' => '#F7F8FC',
            'on-surface' => '#09090A',
            'background' => '#FBFBFF',
            'on-background' => '#09090A',

            'surface-variant' => '#F2F4FA',
            'on-surface-variant' => '#4F4F4F',

            // outline-medium / outline-low do design system.
            'outline' => '#70707A',
            'outline-variant' => '#909098',

            // helper-error (red-300).
            'destructive' => '#FC3A38',
            'on-destructive' => '#FFFFFF',

            // helper-success (green-300).
            'success' => '#00CD0F',
            'on-success' => '#FFFFFF',

            // cyan-primary — usado nos números/stats do terminal da home.
            'accent' => '#12E4D9',
            'on-accent' => '#09090A',

            // Fundo fixo (mesmo valor nos dois temas) pra telas com
            // identidade de marca deliberadamente escura — ex.: a splash.
            'splash-background' => '#09090A',
            'on-splash-background' => '#9C9C9C',
        ],

        'dark' => [
            'primary' => '#782BF1',
            'on-primary' => '#FFFFFF',

            'secondary' => '#9828BD',
            'on-secondary' => '#FFFFFF',

            'surface' => '#0F0F10',
            'on-surface' => '#FDFDFD',
            'background' => '#09090A',
            'on-background' => '#FDFDFD',

            'surface-variant' => '#131314',
            'on-surface-variant' => '#9C9C9C',

            'outline' => '#7C7C83',
            'outline-variant' => '#2C2C2D',

            // helper-error (red-300) — mesmo valor do light, o design system não varia.
            'destructive' => '#FC3A38',
            'on-destructive' => '#09090A',

            'success' => '#00CD0F',
            'on-success' => '#09090A',

            'accent' => '#12E4D9',
            'on-accent' => '#09090A',

            'splash-background' => '#09090A',
            'on-splash-background' => '#9C9C9C',
        ],

        // Corner radii do He4rt: --border-radius-{sm,md,lg,pill}.
        'radius-sm' => 8,
        'radius-md' => 16,
        'radius-lg' => 24,
        'radius-full' => 9_999,

        // Font size scale (points / sp).
        'font-sm' => 14,
        'font-md' => 16,
        'font-lg' => 20,
        'font-xl' => 24,
    ],

    /*
    |---------------------------------------------------------------------------
    | Fonts
    |---------------------------------------------------------------------------
    |
    | Satoshi (sans) e Fira Code (mono) são as duas famílias do design system
    | web — convertidas de app-modules/he4rt/resources/fonts/ (woff2 → ttf via
    | woff2_decompress) pra resources/fonts/ deste projeto. "Cal-Sans" também
    | existe como token no CSS do site, mas não tem arquivo de fonte versionado
    | lá — não foi portado.
    |
    */

    'fonts' => [
        'default' => 'Satoshi-Regular',
        'medium' => 'Satoshi-Medium',
        'bold' => 'Satoshi-Bold',
        'mono' => 'FiraCode-Regular',
        'mono-medium' => 'FiraCode-Medium',
    ],

];
