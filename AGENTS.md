# Randevu Agents

Android-first NativePHP Mobile v4 + Laravel 13 app. Local SQLite per device, no server DB.

## Skills (read these first)

- `.opencode/skills/randevu/SKILL.md` — domain, relative-time rules, file map, EDGE UI conventions, theme rules.
- `.opencode/skills/gh-issues/SKILL.md` — task-by-task lifecycle: refine → plan → claim → implement → review → test → branch from `main` + PR. One task at a time.
- `.opencode/skills/nativephp-clean/SKILL.md` — clean NativePHP v4 code; docs-first protocol (verify APIs in latest docs + vendor source).

## Workflow

1. `php -v; composer -V` baseline, then `gh issue view <n>` and claim before editing.
2. Jump-first dev: `php artisan native:jump` (scan QR). APK later via Bifrost/cloud build.
3. Verify every change: `php artisan test --compact`.
4. Never edit an `in-progress` issue owned by someone else.

## Key paths

- `app/Models/Randevu.php`, `app/Services/RandevuTime.php`
- `app/NativeComponents/`, `resources/views/native/`, `routes/mobile.php`
- `tests/Unit/RandevuTimeTest.php`, `tests/Feature/RandevuTest.php`
