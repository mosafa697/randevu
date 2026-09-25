# Randevu Agents

Android-first NativePHP Mobile v4 + Laravel 13 app. Local SQLite per device, no server DB.

## Skills (load the matching one before working — skill tool or read the file)

- `.opencode/skills/randevu/SKILL.md` — domain, relative-time rules, file map, EDGE UI conventions, theme rules. Load for any app change.
- `.opencode/skills/nativephp-clean/SKILL.md` — clean NativePHP v4 code; docs-first protocol (verify APIs in latest docs + vendor source). Load before writing/editing Blade or components.
- `.opencode/skills/review/SKILL.md` — read-only review gate: NativePHP clean-code + domain + tests + scope. Load on "review", before commit/push/PR, and at gh-issues step 5.
- `.opencode/skills/gh-issues/SKILL.md` — task-by-task lifecycle: refine → plan → claim → implement → review → test → branch from `main` + PR. One task at a time.

## Workflow

1. `php -v; composer -V` baseline, then `gh issue view <n>` and claim before editing.
2. Jump-first dev: `php artisan native:jump` (scan QR). APK later via Bifrost/cloud build.
3. Verify every change: `php artisan test --compact`.
4. Review before shipping: run the `review` skill (read-only) and fix Blockers/Majors before commit/push/PR.
5. Never edit an `in-progress` issue owned by someone else.

## Key paths

- Models `app/Models/`: `Randevu.php`, `Setting.php`; services `app/Services/`: `RandevuTime.php`, `RandevuHijri.php`, `AppTheme.php`, `AppLocale.php`.
- Screens `app/NativeComponents/`, views `resources/views/native/`, routes `routes/mobile.php`.
- Tests `tests/Unit/`, `tests/Feature/` (dates/phrasing, Hijri, theme, screens).
