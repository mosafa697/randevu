# Randevu Deploy Tracker (Android / Google Play)

Target: `randevu.app` — permanent after first Play upload. Never change it.
Build path: Bifrost cloud (this PC has no Android Studio/Gradle per `native:debug`).
First track: Internal. NativePHP Mobile 4.5.2.

## Deploy config — DONE

- [x] Published `config/nativephp.php` (`vendor:publish --tag=nativephp-mobile-config`)
  - `supported_locales = ['en']` (`app.locale = ar` auto-first; 4.6+ reads it, 4.5 ignores harmlessly)
  - Android theme colors → brand violet `#5F53D0` / night `#9488EF`
  - `cleanup_env_keys` += `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_PASSWORD`, `APP_STORE_*`
  - Orientation portrait-only, `status_bar_style: auto` (unchanged, correct)
- [x] `.env.example` += `NATIVEPHP_APP_ID=randevu.app`, `NATIVEPHP_APP_VERSION=1.0.0`,
      `NATIVEPHP_APP_VERSION_CODE=1`, `ANDROID_*` placeholders, SDK comments
- [x] `.gitignore` += `/nativephp/credentials/`, `/nativephp/android/`, `*.keystore`, `*.jks`, service-account JSONs
- [x] Icon/splash present: `public/icon.png`, `public/splash.png`, `public/splash-dark.png`
- [x] Tests: 217/218 pass. 1 pre-existing date-sensitive failure on clean `main`:
      `PeriodDisplayTest::test_create_without_time_stores_null_time` (expects "In 1 month, 10 days",
      gets 9). `native:validate` Windows-path errors also pre-existing, unrelated.

## Release — TODO (needs secrets, run at release time)

- [ ] `cp .env .env.local-backup` (keep dev env)
- [ ] Fill `.env`: `NATIVEPHP_APP_ID=randevu.app`
- [ ] `php artisan native:credentials android` (writes `nativephp/credentials/android/` + `.env`, git-ignored)
- [ ] `php artisan native:release minor` → sets `NATIVEPHP_APP_VERSION=1.0.0`, bumps `VERSION_CODE`
- [ ] Production `.env`: `APP_ENV=production`, `APP_DEBUG=false`
      (Staging/tester builds: `APP_ENV=staging` — locked to internal/alpha, can never go production.)
- [ ] `php artisan test --compact` green (except known pre-existing failure above)
- [ ] Jump check in light AND dark mode
- [ ] Bifrost cloud build → signed `app-release.aab`
      (Local alternative, not recommended here: install Android Studio + SDK + Gradle,
      then `php artisan native:package android --build-type=bundle`)

## Google Play — TODO (Play Console, $25 one-time account)

- [ ] Create app with package `randevu.app`
- [ ] Store listing: icon, feature graphic, screenshots (light + dark), ar + en descriptions, category, contact email
- [ ] Compliance: content rating, target audience, Data Safety (offline SQLite → no data collected/shared),
      privacy-policy URL (required even for offline apps), declarations
- [ ] Service account (Google Cloud) with Play access → JSON key → Bifrost /
      `native:package --google-service-key=... --upload-to-play-store --play-store-track=internal`
- [ ] Upload AAB → Internal track → tester install via Play link
- [ ] Promote internal → closed → production
- [ ] Every update: bump version code (auto via `native:release` / Play version check), upload new AAB
