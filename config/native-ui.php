<?php

/**
 * Native UI — Theme Tokens
 *
 * Published via `php artisan vendor:publish --tag=native-ui-config`.
 * Edit to customize your app's visual identity in one place.
 *
 * For dynamic per-tenant theming, use Native\Mobile\UI\Theme::merge([...])
 * from a service provider. Runtime merges deep-merge on top of these values.
 *
 * Decision log: /docs/NATIVE-UI-REWRITE-PLAN.md (D — theme layer)
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
    | AA deviations from the design spec (all spec pairs failed 4.5:1):
    |   - light.on-surface-variant #69647D (spec #8B879C = 3.5:1 on white)
    |   - light.primary #6F63DB (spec #7C6FE0 + white = 4.05:1)
    |   - light.on-accent #2B2740 (spec white on #DD8A44 = 2.65:1)
    |
    */

    'theme' => [

        'light' => [
            // Gregorian-anchored violet — filled buttons, active states, key accents.
            // 5.78:1 white on primary; 5.49:1 primary text on background.
            'primary' => '#5F53D0',
            'on-primary' => '#FFFFFF',
            // Darkened primary for text on tonal fills (primary/15 over
            // background is 4.46:1 with plain primary — fails).
            'primary-on-soft' => '#5348C2',

            // Surface = cards, sheets, dialogs. Background = page root.
            'surface' => '#FFFFFF',
            'on-surface' => '#2B2740',
            'background' => '#FBF9F4',
            'on-background' => '#2B2740',

            // Surface variant = filled text fields, muted tonal surfaces.
            'surface-variant' => '#F3EFE6',
            // AA-adjusted from spec #8B879C (3.5:1 on white — fails 4.5:1).
            'on-surface-variant' => '#69647D',

            // Tonal fill behind primary actions (chips, pill hovers).
            'primary-soft' => '#EDEAFB',

            // `secondary` variant (FilledTonalButton / borderedProminent):
            // solid tonal container + dark-violet content (5.25:1).
            'secondary' => '#E2DFF8',
            'on-secondary' => '#5348C2',

            // Hijri-anchored amber accent — Today badge, Hijri emphasis.
            // Never use `accent` as text on light surfaces (2.69:1) —
            // use `accent-text` instead.
            'accent' => '#DD8A44',
            // AA-adjusted from spec #DD8A44 + white (2.65:1 — fails).
            'on-accent' => '#2B2740',

            // Tonal fill behind accent elements.
            'accent-soft' => '#FBEEE0',
            // 6.00:1 on white, 5.26:1 on accent-soft.
            'accent-text' => '#9A4F0E',

            // Input field borders (3.33:1+ on surface/background).
            // Decorative card/outline tokens stay subtle by design.
            'field-border' => '#8B869E',

            // Destructive actions — maps to `variant="destructive"` on
            // components and `text-theme-destructive` on inline error text.
            'destructive' => '#B3261E',
            'on-destructive' => '#FFFFFF',

            // Neutral borders. Inputs/selects are theme-styled (Model 3:
            // per-instance border classes are ignored) and the renderers
            // hard-map the unfocused input border to `outline`, so it
            // carries the 3:1 field value (3.50:1 on surface). Cards keep
            // the subtle `outline-variant`.
            'outline' => '#8B869E',
            'outline-variant' => '#D8D4C8',

            // Circular progress-ring / bar backgrounds.
            'progress-track' => '#EDEAE0',
        ],

        'dark' => [
            // 6.04:1 on-primary on primary; reduced glare on thin strokes.
            'primary' => '#9488EF',
            'on-primary' => '#14142A',
            // Lightened primary for text on tonal fills (plain primary on
            // primary/15 over surface is 4.27:1 — fails).
            'primary-on-soft' => '#A79DFF',

            'surface' => '#1E1E3A',
            'on-surface' => '#E8E5F6',
            'background' => '#14142A',
            'on-background' => '#E8E5F6',

            'surface-variant' => '#262646',
            // Was #8E8AB5 (4.46:1 on surface-variant — fails); 5.68:1 now.
            'on-surface-variant' => '#A19EC6',

            'primary-soft' => '#2A2856',

            // Tonal container + light-violet content (6.95:1).
            'secondary' => '#35326B',
            'on-secondary' => '#C9C2FF',

            'accent' => '#F0A868',
            'on-accent' => '#14142A',

            'accent-soft' => '#3A2E22',
            // 6.59:1 on accent-soft.
            'accent-text' => '#F0A868',

            // Input field borders (3.28:1+ on surface/background).
            'field-border' => '#6F6C98',

            'destructive' => '#F2B8B5',
            'on-destructive' => '#14142A',

            // Same renderer hard-map as light: 3.28:1 on surface.
            'outline' => '#6F6C98',
            'outline-variant' => '#3A3A60',

            'progress-track' => '#33335A',
        ],

        // Corner radii (points / dp).
        'radius-sm' => 4,
        'radius-md' => 8,
        'radius-lg' => 16,
        'radius-full' => 9999,

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
    | with `php artisan native:font Inter --default`. Per-element `font`
    | attributes and font-serif / font-mono classes still win over the default.
    |
    |   'fonts' => [
    |       'default' => 'Inter-Regular',
    |       'accent'  => 'DynaPuff-Regular',
    |   ],
    |
    */

    'fonts' => [
        'default' => 'Amiri-Regular',
        'heading' => 'Amiri-Bold',
        'label' => 'Tajawal-Bold',
        'body' => 'Amiri-Regular',
    ],

];
