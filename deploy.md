# Randevu Deploy Tracker (Android / Google Play)

Target: `randevu.app` — permanent after first Play upload. Never change it.
Build path: LOCAL (free) — Android Studio + SDK + Gradle on this PC (chosen over Bifrost cloud, paid).
First track: Internal. NativePHP Mobile 4.5.2.

## Deploy config — DONE

- [x] Published `config/nativephp.php` (`vendor:publish --tag=nativephp-mobile-config`)
  - `supported_locales = ['en']` (`app.locale = ar` auto-first; 4.6+ reads it, 4.5 ignores harmlessly)
  - Android theme colors → brand violet `#5F53D0` / night `#9488EF`
  - `cleanup_env_keys` += `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_PASSWORD`, `APP_STORE_*`
  - Orientation portrait-only, `status_bar_style: auto` (unchanged, correct)
- [x] `.env.example` += `NATIVEPHP_APP_ID=randevu.app`, `NATIVEPHP_APP_VERSION=1.0.0`,
      `NATIVEPHP_APP_VERSION_CODE=1`, `ANDROID_*` placeholders, SDK comments
- [x] `.gitignore` += `/nativephp/credentials/`, `/nativephp/android/`, `/credentials/`, `*.keystore`, `*.jks`, service-account JSONs
- [x] Icon/splash present: `public/icon.png`, `public/splash.png`, `public/splash-dark.png`
- [x] Tests: 231/231 pass (2026-10-08, production env). Old note: 217/218 with 1 pre-existing
      date-sensitive failure on clean `main` is now fixed (pin-clock commit).

## Release — DONE (2026-10-08, first Play upload 1.0.0 / code 1)

- [x] `cp .env .env.local-backup` (keep dev env) — DONE, backup holds dev + APP_ID, no secrets
- [x] Fill `.env`: `NATIVEPHP_APP_ID=randevu.app` — DONE
- [x] `php artisan native:credentials android` — DONE
      (v4.5.2 writes `credentials/app-release-key.jks` at project root + `ANDROID_*` in `.env`, git-ignored;
      not `nativephp/credentials/android/` as older docs said)
- [x] `native:release minor` — DONE / SKIPPED for first upload: already `1.0.0` / code `1`;
      `minor` would jump to `1.1.0`. Command only bumps the name; code bumps at package time.
- [x] Production `.env`: `APP_ENV=production`, `APP_DEBUG=false` — DONE
      (Staging/tester builds: `APP_ENV=staging` — locked to internal/alpha, can never go production.)
- [x] `php artisan test --compact` green — DONE, 231/231 pass in production env
- [ ] Jump check in light AND dark mode
- [ ] Local build → signed `app-release.aab` (FREE path, no Bifrost)
  - [ ] Step 1 — Install Android Studio (2024.2.1+, `developer.android.com/studio`, defaults)
  - [ ] Step 2 — SDK Manager: **SDK Platforms** → Android 16 (API 36, covers `compile_sdk 36`);
        **SDK Tools** → Build-Tools + Platform-Tools. SDK path default:
        `C:\Users\<you>\AppData\Local\Android\Sdk`
  - [ ] Step 3 — Install 7-Zip to default `C:\Program Files\7-Zip\7z.exe`
        (Windows requirement, already wired in `config/nativephp.php`)
  - [ ] Step 4 — Env vars (Windows): `ANDROID_HOME=<sdk path>`,
        `PATH=%PATH%;%ANDROID_HOME%\platform-tools`.
        `JAVA_HOME` only if Gradle complains (PC has Java 19; Gradle pairs best with 17,
        Studio's bundled JBR is the fallback via `NATIVEPHP_GRADLE_PATH`)
  - [ ] Step 5 — Verify: `java -version`, `adb devices`, `php artisan native:debug`
        (Studio + Gradle must flip from "Not found" to version numbers;
        else set `NATIVEPHP_ANDROID_SDK_LOCATION=<sdk path>` in `.env`)
  - [ ] Step 6 — `php artisan native:install` (creates git-ignored `nativephp/` project,
        downloads embedded PHP — first run is slow, leave the terminal alone)
  - [ ] Step 7 — `php artisan native:package android --build-type=bundle`
        (signs with `credentials/app-release-key.jks` from `.env` `ANDROID_*` keys)
  - [ ] Step 8 — Confirm artifact:
        `nativephp/android/app/build/outputs/bundle/release/app-release.aab`
        (Cloud alternative, paid: Bifrost `bifrost.nativephp.com` — packs from $5/5 builds.)

## Google Play — TODO (Play Console, $25 one-time account)

- [x] Create app with package `randevu.app`
- [ ] Store listing: icon, feature graphic, screenshots (light + dark), ar + en descriptions, category, contact email
- [ ] Compliance: content rating, target audience, Data Safety (offline SQLite → no data collected/shared),
      privacy-policy URL (required even for offline apps), declarations
- [ ] Service account (Google Cloud) with Play access → JSON key → Bifrost /
      `native:package --google-service-key=... --upload-to-play-store --play-store-track=internal`
- [ ] Upload AAB → Internal track → tester install via Play link
- [ ] Promote internal → closed → production
- [ ] Every update: bump version code (auto via `native:release` / Play version check), upload new AAB
