# Randevu Agents

Android-first NativePHP Mobile v4 + Laravel 13 app. Local SQLite per device, no server DB.

## Skills (read these first)

- `.opencode/skills/randevu/SKILL.md` — domain, relative-time rules, file map, EDGE UI conventions.
- `.opencode/skills/gh-issues/SKILL.md` — issue claim protocol (assign + `ready`→`in-progress`, one branch per issue).

## Workflow

1. `php -v; composer -V` baseline, then `gh issue view <n>` and claim before editing.
2. Jump-first dev: `php artisan native:jump` (scan QR). APK later via Bifrost/cloud build.
3. Verify every change: `php artisan test --compact`.
4. Never edit an `in-progress` issue owned by someone else.

## Key paths

- `app/Models/Randevu.php`, `app/Services/RandevuTime.php`
- `app/NativeComponents/`, `resources/views/native/`, `routes/mobile.php`
- `tests/Unit/RandevuTimeTest.php`, `tests/Feature/RandevuTest.php`
