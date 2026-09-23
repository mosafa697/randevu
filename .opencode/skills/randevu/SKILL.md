# Randevu Skill

Android-first NativePHP Mobile v4 app. Users track future appointments and past memories with distance-from-today phrasing.

## Domain

- Model `App\Models\Randevu`: `title`, `occurs_on` (date), `note` (nullable).
- Rule: `occurs_on >= today` = appointment, `< today` = memory. Today counts as appointment and gets a Today badge.
- Service `App\Services\RandevuTime`:
  - `dayCount($date, $today = null)` signed int (future +, past -).
  - `phrase(...)`: Today / Tomorrow / Yesterday / `In N days` / `N days ago` (2-29) / `In N months` / `N months ago` (30-364) / `In N years` / `N years ago` (365+).
- Scopes: `upcoming()` ASC (includes today), `memories()` DESC, `today()`.
- Validation: `Randevu::rules()` — title required max 255, occurs_on required date, note nullable max 2000.

## Where code lives

- `database/migrations/2026_09_23_000001_create_randevus_table.php` — SQLite table (local per device, auto-migrated on boot).
- `app/Models/Randevu.php`, `app/Services/RandevuTime.php`
- Screens `app/NativeComponents/`: `Follow.php`, `RandevuCreate.php`, `RandevuEdit.php`
- Views `resources/views/native/`: `follow.blade.php`, `randevu-create.blade.php`, `randevu-edit.blade.php`
- Routes `routes/mobile.php`: `/` Follow, `/create` Create, `/edit/{id}` Edit via `Route::native()`.
- Tests: `tests/Unit/RandevuTimeTest.php`, `tests/Feature/RandevuTest.php`.

## UI conventions (EDGE / SuperNative)

- Tags use `<native:*>`: `column`, `row`, `text`, `outlined-text-input` with `native:model="prop"`, `button` with `@press="method"`, `pressable` with `@navigate="'/path'"`.
- Screens extend `Native\Mobile\Edge\NativeComponent`, state is public props, navigation via `$this->navigate()`, `$this->replace()`, `$this->back()`, params via `$this->param('id')`.
- Follow view: Today section first, then Coming up (soonest first), then Memories (newest first). Each card shows relative phrase + absolute date (`d M Y`) + exact day count. Empty state invites first randevu.
- Forms keep old values on validation failure (public props persist), show per-field `$errors`.

## Storage

- SQLite only, per-device = per-user. No server DB, no credentials in app. `SESSION/CACHE=file`, `QUEUE=sync`.
- Dev via Jump: `php artisan native:jump`, scan QR with Jump app. APK later via Bifrost/cloud build.
