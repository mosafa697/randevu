---
name: php-native-modern-ui
description: Use whenever building, styling, or redesigning UI in native/vanilla PHP (no framework like Laravel or Symfony) — pages, forms, admin panels, dashboards, auth screens, includes/partials. Prevents the generic, dated, copy-pasted Bootstrap-card look that native-PHP projects tend toward, and enforces a distinct design identity, reusable PHP partials, and modern CSS per project. Trigger this even for vague asks like "make this page look better", "redesign my site", "fix the UI", "this looks like every other PHP tutorial site", or when adding a new page/form to an existing native-PHP app.
license: MIT
---

# Modern UI for Native PHP

Native PHP projects (no framework, no component system) tend to drift into one look: a Bootstrap CDN link, `.container > .card > .form-group`, the same navbar copy-pasted into every file, and a jQuery datepicker. That's not "PHP's fault" — it's a structure problem (no shared layout) and a design-default problem (never deciding on an identity). Fix both.

## Step 0 — Diagnose before touching CSS

Look at the existing files (or ask to see 2–3 representative pages) and check for:
- Repeated `<head>`, nav, and footer markup pasted into every `.php` file instead of an `include`/`require`.
- No single source of truth for color/spacing/type (colors hardcoded per-file, inconsistent spacing).
- Bootstrap/Bulma/W3.CSS loaded from a CDN with only default theming touched.
- Inline `style=""` attributes scattered through templates.
- Forms styled with browser defaults or the framework's default `.form-control`.

Whichever of these apply, they're your work items — call them out to the user in one line, then fix them as part of the redesign, not just the visual layer.

## Step 1 — Structure first: stop copy-pasting markup

Before any visual work, make the page shell reusable. This is the highest-leverage fix for "every page looks slightly different but all look bad."

```
/public/index.php
/public/dashboard.php
/partials/header.php     <- <head>, <html>, opening <body>, nav
/partials/footer.php     <- closing tags, shared scripts
/partials/flash.php      <- shared success/error message markup
/assets/css/tokens.css   <- design tokens (see Step 2)
/assets/css/base.css     <- resets + global element styles
/assets/css/components.css
```

Each page becomes:
```php
<?php require __DIR__ . '/../partials/header.php'; ?>
<main class="page page--dashboard">
  ...page content...
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
```

If the project already has a partial system, use it — don't invent a second one. If it doesn't, introduce exactly this one and migrate existing pages to it as part of the task rather than adding a new one-off page next to the old inconsistent ones.

See `references/partial-example.php` for a filled-out header partial (nav active-state handling, `htmlspecialchars` on dynamic values, one `<link>` per stylesheet).

## Step 2 — Give the project an actual design identity

Do not reach for Bootstrap defaults or a generic "SaaS card" look. Read `/mnt/skills/public/frontend-design/SKILL.md` if it's available in this environment and apply its planning process; if it isn't available, follow this condensed version:

1. **Name the subject.** What is this app for and who uses it? An internal invoicing tool for a small workshop looks nothing like a public recipe blog — let that drive every choice below.
2. **Pick 4–6 named colors** (hex values) — not "Bootstrap blue" (#0d6efd) or Tailwind's default slate/indigo defaults.
3. **Pick 1–2 typefaces** with a real type scale (e.g. a fluid `clamp()` scale), not the browser default stack alone.
4. **Pick a layout concept in one sentence** (e.g. "dense left-aligned data table with a fixed sidebar" vs "centered single-column form flow") and sketch it in ASCII if it helps.
5. **Write it down** in `assets/css/tokens.css` as CSS custom properties (starter file in `references/starter-tokens.css`) so every page and every future page pulls from the same source instead of re-deciding colors per file.

Avoid these tells specifically — they're what makes native-PHP admin panels and portfolio sites look interchangeable:
- Bootstrap's default navbar-with-shadow + `.container` centered layout, untouched.
- Every content block wrapped in an identical white rounded card with the same `box-shadow: 0 2px 4px rgba(0,0,0,.1)`.
- A single accent blue used for links, buttons, and focus states with no other color decisions made.
- Font stack left at `Arial, sans-serif` or the Bootstrap default (`-apple-system, ...`) with no type scale.
- All-caps tracked-out section labels, and a `→` tacked onto every button/link.

## Step 3 — Modern CSS, no framework needed

Native PHP doesn't need Bootstrap/jQuery for a modern result — plain CSS now covers what those used to require:

- **Layout:** CSS Grid and Flexbox for everything; no float-based grids, no 12-column class soup (`col-md-4 col-sm-6`) unless the project explicitly wants a framework.
- **Tokens:** CSS custom properties on `:root` for color, spacing, radius, font — defined once in `tokens.css`, referenced everywhere (`var(--space-4)`), never re-hardcoded per component.
- **Responsiveness:** `clamp()` for fluid type/spacing instead of a stack of breakpoints copy-pasted per component; container queries (`@container`) for components that need to adapt to their own container rather than the viewport, where browser support fits the project's audience.
- **Forms:** style native elements directly (`input`, `select`, `textarea`, `button`) with `:focus-visible`, `:invalid`, `:user-invalid` — skip jQuery plugins and Bootstrap's `.form-control` boilerplate. `<dialog>` and the Popover API can replace most modal/dropdown JS libraries.
- **Interactivity:** vanilla JS (`fetch`, `<template>`, event delegation) is almost always enough for a native-PHP app's needs; reach for a library only if the user already has one or the interaction genuinely warrants it (e.g. a rich charting need).
- **One stylesheet per concern**, not one 2,000-line `style.css`: `tokens.css` → `base.css` (resets, element defaults) → `components.css` (buttons, cards, forms) → optional per-page file for page-specific layout only.

## Step 4 — Accessibility & quality floor (non-negotiable, don't announce it)

- Semantic HTML first (`<nav>`, `<main>`, `<button>` not `<div onclick>`).
- Visible keyboard focus (`:focus-visible`, never `outline: none` without an equally visible replacement).
- Color contrast holds at the chosen palette, not just the accent color.
- Every `<img>` has real `alt`; every form `<input>` has an associated `<label>`.
- Test the layout down to a ~360px mobile width, not just resizing a desktop window slightly.

## Step 5 — PHP-specific correctness while you're in the templates

Since you're touching these files for UI anyway, don't introduce or leave XSS holes:
- Every piece of dynamic data echoed into HTML goes through `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` (or a small `e()` helper that wraps it — add one to a `helpers.php` if the project doesn't have one).
- Forms that mutate state need a CSRF token in a hidden field, checked on submit — flag this if missing, even though it's outside pure "UI," since it's usually the same `<form>` markup you're already restyling.

## Step 6 — Before delivering, self-check

- Did I replace copy-pasted head/nav/footer markup with `require`d partials, or is there still duplication?
- Is there one `tokens.css` that every page pulls colors/spacing/type from?
- Would this page be visually distinguishable from a generic Bootstrap admin theme or Tailwind starter kit? If not, revise the palette/type/layout choice, not just the copy.
- Mobile width, keyboard focus, and label/alt coverage checked.

## Reference files

- `references/starter-tokens.css` — a starter CSS custom-properties file (colors, spacing, type scale) to copy in and then customize per project — never ship it unedited, it's a starting skeleton, not a final palette.
- `references/partial-example.php` — example `header.php`/`footer.php` partial pair showing nav active-state, escaping, and single stylesheet includes.
- `references/anti-patterns.md` — longer list of the specific native-PHP UI tells to avoid, with the fix for each.
