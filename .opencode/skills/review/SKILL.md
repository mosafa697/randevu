---
name: review
description: Use when asked to review code, a diff, a branch, or a PR in this repo ("review", "code review", "check my changes", "is this clean?"), before any commit/push/PR, and for step 5 (Review) of gh-issues. Read-only NativePHP Mobile v4 clean-code gate: EDGE/Blade directive hard rules, component state, theme tokens, domain rules, scope, tests, secrets.
---

# Review Skill (read-only clean-code gate)

Review the real diff against the issue contract and NativePHP clean code. Never edit, commit, push, or open a PR while reviewing — report findings, then STOP.

## 1. Gather context first (never review from memory)

1. `git status` + the real diff: `git diff main...HEAD` (branch) or `git diff` (uncommitted). Review changed hunks only.
2. `gh issue view <n>` — the issue's "Done when" list is the review contract.
3. Load the rules the diff must obey: `nativephp-clean` (hard rules), `randevu` (domain/UI/theme), `gh-issues` (scope, one task at a time).
4. Check the target version: `php artisan native:version`. If docs and vendor disagree, trust `vendor/nativephp/mobile/src/` — source beats docs beats memory.

## 2. Scope and hygiene

- Every hunk traces to the issue's "Done when". Out-of-scope edits and drive-by refactors = Major; ask before accepting.
- No debug leftovers: `dd`, `dump`, `ray`, noisy `Log::`, commented-out code, unused imports/props.
- No secrets: `.env` untouched, no keys/tokens committed. SQLite per device only — no server DB.
- One screen = one concern. Shared date/selection logic belongs in `app/Services`; shared chrome in one `NativeLayout`. Flag copy-paste between components.

## 3. NativePHP / EDGE hard rules (Blocker when violated)

- `@press="methodName"` — bare method only; arguments or `$this->` expressions are Blockers.
- Navigation only via `@navigate`: `@navigate="'/path'"`, `@navigate="'/edit/'.$item['id']"`, `@navigate.back`. Never `@navigate="/path"`, never `{{ }}` inside a directive argument.
- `native:model` binds a public **string** prop; `:options` references a public **array** prop, never a method call.
- Every `native:text` has an explicit theme token (`text-theme-*`); raw palettes (`bg-slate-800`) break dark mode = Blocker.
- Public component state is scalar-only (string/int/bool/array): never cache Eloquent models; reload with `findOrFail()` per action.
- Navigation from PHP via `$this->navigate()` / `replace()` / `back()`; `$this->param('id')` for route params; every screen declares `navTitle()`.
- Icons pass per-platform pairs (`ios:` + `android:`).

## 4. Domain rules

- `occurs_on` (Gregorian) is source of truth; `>= today` = appointment, today gets the Today badge, `< today` = memory. Follow: Today → Coming up ASC; Memories screen: DESC.
- Relative phrasing comes from `RandevuTime` (`dayCount`/`phrase`/`phraseFor`); Hijri from `RandevuHijri` — no inline date math, no `intl` dependence. `phraseFor` unit matrix: Today/Tomorrow/Yesterday always special; days-only exact; single unit rounds; all units exact breakdown; off middle units roll down; no units falls back to `phrase()`.
- Validation uses `Randevu::rules()`; per-field `$errors` shown; old values survive failed saves; `hasAnyUnit()` blocks saving with all distance units off.
- Color: only via `COLOR_PRESETS` + `normalizeColor()` (uppercase `#RRGGBB`, empty → null); never raw unvalidated input.
- Theme: mode is per-device via `AppTheme` (light|dark) — forced palette in both blocks + `TailwindParser::clearCache()`; toggling must stay lossless. No raw palette classes in Blade; drawn chrome uses `AppTheme::token()`.
- Locale: per-device via `AppLocale` (ar|en, ar default); all visible strings through `__('randevu.*')`; `Setting` is the only per-device store.

## 5. Verification evidence (re-run it; never trust a summary)

- `php artisan test --compact` green, covering save/update/delete AND error paths (`Native::test()`, `assertReplacedWith`, `assertSet`). Theme/locale changes must not break `AppThemeTest`, `ThemeTest`, `ThemeToggleTest`, `PeriodDisplayTest`.
- Touched Blade passes the TRUE precompile lint (`NativeTagPrecompiler::setActive(true)` → `compileString` → `php -l`), then `view:clear`. `view:cache` evidence is invalid.
- Both modes: every new text has a theme token and the screen was checked in Jump in light AND dark (ask which modes; unverified dark mode = Major).

## 6. Report format

- Verdict first: **PASS** / **PASS WITH NITS** / **BLOCKED**.
- Findings by severity — Blocker (crash, data loss, secret, dark-mode break, rule above), Major (wrong behavior, scope creep, missing tests), Minor, Nit — each as `file:line` — evidence — concrete fix, naming the rule violated.
- List the exact commands run and their results. Then STOP; fix nothing unless the user asks.

## Rules

- Read-only: no edits, commits, pushes, or PRs from a review — ever.
- Review the diff, not the intent; cite `file:line` for every finding.
- One task at a time; do not fold in unrelated findings as required fixes.
