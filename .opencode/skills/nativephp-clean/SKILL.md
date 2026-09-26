---
name: nativephp-clean
description: Use before writing or reviewing NativePHP Mobile v4 (SuperNative/EDGE) Blade or NativeComponents — docs-first API verification, Blade directive hard rules, component state, layouts, native test/precompile verification.
---

# NativePHP Clean-Code Skill

Write idiomatic NativePHP Mobile v4 (SuperNative + EDGE). The framework moves fast — **verify every API against the latest docs before using it**, not from memory.

## Docs-first protocol (mandatory)

1. Check the installed version: `php artisan native:version`.
2. Read the matching docs page: `https://nativephp.com/docs/mobile/4/<section>/<page>` (v4 = SuperNative/EDGE; older versions differ).
3. Cross-check the real implementation in `vendor/nativephp/mobile/src/` — source beats memory:
   - Directives/precompiler: `src/Edge/NativeTagPrecompiler.php`
   - Components: `src/Edge/Elements/`, layouts: `src/Edge/Layouts/`
   - Testing harness: `src/Testing/Native.php`, `src/Testing/TestableComponent.php`
4. If docs and vendor disagree, trust vendor + pin the version you verified against.

## Blade directives (hard rules — all learned from real crashes)

- `@press="methodName"` — **bare method name only**. No arguments, no `$this->` expressions. Need a parameter? Write a dedicated method (`useHijri()`), never `@press="setMode('x')"`.
- Navigation ONLY via `@navigate`: quote style `@navigate="'/path'"`, expression style `@navigate="'/edit/'.$item['id']"`, boolean `@navigate.back`. NEVER `@navigate="/path"` (compiles to unquoted, invalid PHP) and NEVER `{{ }}` inside a directive argument (the precompiler captures it raw → ParseError on device).
- `native:model="prop"` binds to a **public string property**. Select `:options` must reference a **public array prop** (`:options="$dayOptions"`), never a method call.
- For binary/segmented choices (language, theme, calendar mode) use a row of `native:button` with bare `@press` methods + `:variant="$x === '…' ? 'primary' : 'ghost'"` highlighting — `@press` is the proven on-device path. Avoid `native:button-group` + `@change`: its `native:model` sync callback shares the single `on_change` slot (duplicate `_change` key — last wins at compile), and `@change` handlers that read the bound prop replay the stale selection, looking dead on device. Test interactions with `->press('method')`, which follows the same wire path as the device.
- Every `native:text` gets an explicit theme token — colors do NOT inherit; unprefixed text renders black in dark mode.

## Components

- Extend `Native\Mobile\Edge\NativeComponent`. Keep public state **scalar-only** (string/int/bool/array): reload Eloquent models per action (`findOrFail()`), never hold a model in public state (shared-memory sync).
- Navigation from PHP: `$this->navigate()`, `$this->replace()`, `$this->back()`; read route params via `$this->param('id')`; give every screen a `navTitle()` for layout chrome.
- One screen = one concern. Shared date/selection logic goes in `App\Services`, shared option lists in static helpers, never copy-pasted across components.

## Layouts and chrome

- Shared chrome (TopBar/tabs) belongs in ONE `NativeLayout` (`navBar()`/`tabBar()` builders), attached via `->layout(...)` — not duplicated per screen. One-off chrome goes inline in that screen only.
- Icons: always pass per-platform pairs (`ios: 'calendar', android: 'calendar_month'`); shared names are unreliable across renderers.

## Verification (no shortcuts)

- `php artisan test --compact` must be green — cover save/update/delete AND error paths via the `Native` harness (`Native::test()`, `Native::visit()`, `assertReplacedWith`, `assertSet`).
- Lint Blade with the TRUE native compile (`NativeTagPrecompiler::setActive(true)` → `compileString` → `php -l`). `view:cache` bypasses the precompiler and gives false confidence. Always `view:clear` after, so devices recompile fresh.
- Final proof is visual in Jump (light AND dark mode) — say which modes you checked.
- Before commit/PR, run the `review` skill (`.opencode/skills/review/SKILL.md`) as the read-only gate; fix Blockers/Majors first.
