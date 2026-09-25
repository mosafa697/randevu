---
name: randevu
description: Use for any Randevu app change — domain rules (Randevu model, RandevuTime relative phrasing, RandevuHijri), file map, EDGE/SuperNative UI conventions, theme tokens, SQLite/Jump setup.
---

# Randevu Skill

Android-first NativePHP Mobile v4 app. Users track future appointments and past memories with distance-from-today phrasing, in Gregorian and Hijri calendars.

## Domain

- Model `App\Models\Randevu`: `title`, `occurs_on` (date, Gregorian, source of truth), `note` (nullable), `color` (nullable `#RRGGBB`), `hijri_year/month/day` (nullable, stored alongside), `entered_in` (`gregorian`|`hijri`), `show_years/show_months/show_days` (distance units the phrase uses).
- Helpers: `COLOR_PRESETS`, `normalizeColor()` (trim/uppercase/prepend `#`, empty → null), `hijriTriple()`, `hasAnyUnit()` (at least one unit stays on).
- Rule: `occurs_on >= today` = appointment, `< today` = memory. Today counts as appointment and gets a Today badge.
- Service `App\Services\RandevuTime`:
  - `dayCount($date, $today = null)` signed int (future +, past -).
  - `phrase(...)`: Today / Tomorrow / Yesterday / `In N days` / `N days ago` (2-29) / `In N months` / `N months ago` (30-364) / `In N years` / `N years ago` (365+).
  - `phraseFor($date, $showYears, $showMonths, $showDays, $today = null)`: per-randevu units — days-only keeps the exact count; a single shown unit rounds; all units give the exact calendar breakdown; switched-off middle units roll down; falls back to `phrase()` when no unit is on.
  - `MONTH_NAMES`, `monthNumber()` for the Gregorian picker.
- Service `App\Services\RandevuHijri`: pure-PHP TABULAR Islamic calendar (no intl, runs in embedded PHP). `fromGregorian`, `toGregorian`, `valid`, `daysInMonth`, `isLeapYear`, `format` ("12 ربيع الثاني 1448"), `MONTH_NAMES` (Arabic), `yearOptions`. Caveat: ±1–2 days vs observed Umm al-Qura; Gregorian stays source of truth.
- Scopes: `upcoming()` ASC (includes today), `memories()` DESC, `today()`.
- Validation: `Randevu::rules()` — title required max 255, occurs_on required date, color nullable `#RRGGBB` regex, note nullable max 2000, hijri_* nullable ints, entered_in in:gregorian,hijri, show_* booleans.
- Model `App\Models\Setting`: per-device key/value store (`get`/`set`), string key primary, no timestamps.
- Service `App\Services\AppTheme` + `AppliesTheme` concern: per-device `light|dark` (default light), `current/apply/persist/toggle/token`. Forces the authored palette into BOTH blocks (the renderer otherwise follows the system scheme) from the `native-ui.authored-theme` snapshot, then clears the process-lifetime `TailwindParser` class cache — without that, the first render's colors win forever.
- Service `App\Services\AppLocale` + `AppliesLocale` concern: per-device `ar|en` (config default `ar`), `current/apply/persist/isRtl`. UI strings live in `lang/{ar,en}/randevu.php` and `validation.php`.

## Where code lives

- `database/migrations/*` — SQLite (local per device, auto-migrated on boot): randevus, hijri columns, settings, period units, color.
- Models `app/Models/`: `Randevu.php`, `Setting.php`.
- Services `app/Services/`: `RandevuTime.php`, `RandevuHijri.php`, `AppTheme.php`, `AppLocale.php`.
- Screens `app/NativeComponents/`: `Follow.php`, `Memories.php`, `RandevuCreate.php`, `RandevuDetails.php`, `RandevuEdit.php`, `Settings.php`, `Layouts/RandevuLayout.php`, `Concerns/` (`AppliesTheme`, `AppliesLocale`, `PicksColor`).
- Views `resources/views/native/`: `follow.blade.php`, `memories.blade.php`, `randevu-create.blade.php`, `randevu-details.blade.php`, `randevu-edit.blade.php`, `settings.blade.php`, `partials/color-picker.blade.php`.
- Routes `routes/mobile.php` (all `Route::native(...)->layout(RandevuLayout::class)`): `/` Follow, `/create` Create, `/memories` Memories, `/details/{id}` Details, `/edit/{id}` Edit, `/settings` Settings.
- Translation: `lang/{ar,en}/randevu.php`, `lang/{ar,en}/validation.php`.
- Tests: `tests/Unit/` `RandevuTimeTest`, `RandevuHijriTest`, `PeriodDisplayTest`, `AppThemeTest`, `ThemeTest`, `AppNameTest`; `tests/Feature/` `RandevuTest`, `RandevuScreensTest`, `PeriodDisplayTest`, `ThemeToggleTest`, `HealthTest`.

## UI conventions (EDGE / SuperNative)

- Tags use `<native:*>`: `column`, `row`, `text`, `outlined-text-input` with `native:model="prop"`, `button` with `@press="methodName"` (bare method only — no args, no `$this->` expressions), `pressable`/`button` navigation ONLY via `@navigate` directive: quote style `@navigate="'/path'"`, expression style `@navigate="'/edit/'.$item['id']"`, boolean `@navigate.back`. NEVER `@navigate="/path"` (compiles to unquoted PHP) and NEVER `{{ }}` inside a directive argument.
- Colors: every `native:text` needs an explicit theme token (`text-theme-on-background`, `text-theme-on-surface`, `text-theme-on-surface-variant`, `text-theme-destructive`). Cards on `bg-theme-surface`. Never rely on inheritance — dark mode falls back to black.
- Date entry: Day/Month/Year `native:select` triples bound to string props (`:options="$dayOptions"` from public array props, NOT method calls). Validate combos with `checkdate()` / `RandevuHijri::valid()`, keep one `errors['occurs_on']` message.
- Calendar mode toggle: `useGregorian`/`useHijri` methods + `@if($calendar_mode === ...)` blocks. Components resolve BOTH calendars on save (`resolveDates()`); edit prefills per `entered_in`.
- Layout: `RandevuLayout` — NavBar title via `navTitle()` plus a sun/moon `toggleTheme()` action, tabs Follow/Memories/New/Settings. Drawn chrome colors come from `AppTheme::token()` literals. Screens declare `navTitle()`; keep inline Back buttons on forms, no inline title rows.
- Follow view: Today section first, then Coming up (soonest first). Memories screen lists past entries newest first. Each card: relative phrase + absolute date (`d M Y`) + Hijri line + exact day count + note (+ optional `color` accent). Empty states invite the first randevu.
- Color: forms use the `PicksColor` trait + `partials/color-picker.blade.php`, storing uppercase `#RRGGBB` (presets in `Randevu::COLOR_PRESETS`); clearing sets null.
- Locale/RTL: all user-visible text via `__('randevu.*')`; Arabic is the default and RTL; every screen applies `AppLocale` on mount.
- Forms keep old values on validation failure (public props persist), show per-field `$errors`.
- Verify Blade with the TRUE native precompile (`NativeTagPrecompiler::setActive(true)` + `compileString` + `php -l`): `view:cache` bypasses the precompiler and gives false confidence. Always `view:clear` after so the device recompiles fresh.

## Attractive theme (grab attention, stay readable)

- Brand lives in ONE place: `config/native-ui.php`, with BOTH `light` and `dark` blocks authored explicitly. `AppTheme` snapshots them to `native-ui.authored-theme` and forces the active mode into both blocks at runtime; `light → dark → light` must stay lossless (`AppThemeTest`).
- Reference ONLY semantic tokens in Blade (`bg-theme-surface`, `text-theme-on-surface`, `border-theme-outline`) — never raw palettes like `bg-slate-800`, or forced modes break. Drawable chrome (NavBar/TabBar) needs literal colors from `AppTheme::token()`.
- Keep every `on-*` color at 4.5:1 contrast against its surface (WCAG AA); the shipped defaults meet it, re-check any customized pair.
- Attention hierarchy per screen: one hero element (big title/phrase), one accent action (primary button or badge), everything else muted (`on-surface-variant`). Cards get `rounded-2xl` + surface fill; sections separated by `divider`, not extra boxes.
- Verify every palette change in Jump in BOTH light and dark mode before merging; `ThemeTest` asserts the Randevu brand tokens and bundled fonts exist.

## Storage

- SQLite only, per-device = per-user. No server DB, no credentials in app. `SESSION/CACHE=file`, `QUEUE=sync`.
- Dev via Jump: `php artisan native:jump`, scan QR with Jump app. APK later via Bifrost/cloud build.
