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
    | TODO: trocar pelas cores oficiais da marca He4rt quando definidas —
    | por enquanto os tokens são os padrões do pacote nativephp/mobile-ui.
    |
    */

    'theme' => [

        'light' => [
            // Primary brand color — used for filled buttons, active states, key accents.
            'primary' => '#0F766E',
            'on-primary' => '#FFFFFF',

            // Secondary / muted action color.
            'secondary' => '#475569',
            'on-secondary' => '#FFFFFF',

            // Surface = cards, sheets, dialogs. Background = page root.
            'surface' => '#FFFFFF',
            'on-surface' => '#1B1B18',
            'background' => '#FAFAFA',
            'on-background' => '#1B1B18',

            // Surface variant = filled text fields, muted tonal surfaces.
            // on-surface-variant = muted label/hint text on those surfaces.
            'surface-variant' => '#FAFAFA',
            'on-surface-variant' => '#706F6C',

            // Outline = neutral borders (text fields, dividers, cards).
            // outline-variant = softer edges: hairline dividers, card seams.
            'outline' => '#E5E5E5',
            'outline-variant' => '#EFEFEF',

            // Destructive actions — maps to `variant="destructive"` on components.
            'destructive' => '#B91C1C',
            'on-destructive' => '#FFFFFF',

            // Success / "safe to proceed" — confirmations, verified badges.
            'success' => '#15803D',
            'on-success' => '#FFFFFF',

            // Tertiary accent — for highlights, badges, emphasis not covered by primary.
            'accent' => '#C2410C',
            'on-accent' => '#FFFFFF',
        ],

        'dark' => [
            // Leave empty or partial to auto-derive from `light` (luminance inversion).
            // Specify any token here to override the derived value.
            'primary' => '#14B8A6',
            'on-primary' => '#FFFFFF',

            'secondary' => '#94A3B8',
            'on-secondary' => '#0F172A',

            'surface' => '#161615',
            'on-surface' => '#EDEDEC',
            'background' => '#0A0A0A',
            'on-background' => '#EDEDEC',

            'surface-variant' => '#1F1F1E',
            'on-surface-variant' => '#A1A09A',

            'outline' => '#3E3E3A',
            'outline-variant' => '#2A2A28',

            'destructive' => '#F87171',
            'on-destructive' => '#0F172A',

            'success' => '#4ADE80',
            'on-success' => '#052E16',

            'accent' => '#FDBA74',
            'on-accent' => '#0F172A',
        ],

        // Corner radii (points / dp).
        'radius-sm' => 4,
        'radius-md' => 8,
        'radius-lg' => 16,
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
    | Semantic names for bundled fonts (resources/fonts/ file tokens, minus
    | the extension). Use an alias anywhere a font token works — the `font`
    | attribute (`font="accent"`), chrome ->font() builders, or the layout
    | $font property. The `default` alias is the app-wide default font:
    | 'System' resolves to the platform face (San Francisco on iOS, Roboto
    | on Android); set a bundled token to apply it everywhere. Download one
    | with `php artisan native:font Inter --default`.
    |
    |   'fonts' => [
    |       'default' => 'Inter-Regular',
    |       'accent'  => 'DynaPuff-Regular',
    |   ],
    |
    */

    'fonts' => [
        'default' => 'System',
    ],

];
