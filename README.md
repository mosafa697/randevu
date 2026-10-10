# Randevu

Randevu is an Android-first app for keeping track of upcoming appointments and remembering past events. It is built with Laravel 13 and NativePHP Mobile v4, and stores its data locally in SQLite on each device. No account or server database is required.

## Features

- Separate views for upcoming appointments and past memories.
- Gregorian and Hijri date entry, with optional times.
- Relative-time descriptions and configurable years, months, days, and hours.
- Notes and colors for personalizing entries.
- Arabic and English, including right-to-left Arabic layout.
- Light and dark themes.

## Install for development

### Requirements

- PHP 8.3 or later and Composer.
- Node.js 20.19+ or 22.12+, and npm (required by Vite 8).
- An Android device with the NativePHP Jump app for live development.

### Set up the project

Clone the repository and install its dependencies from the project directory:

```sh
git clone https://github.com/mosafa697/randevu.git
cd randevu
composer run setup
```

The setup script installs PHP and JavaScript dependencies, creates `.env` from `.env.example` if needed, generates the Laravel application key, prepares the SQLite database, and builds the frontend assets.

### Database migrations and demo data

`composer run setup` runs migrations during initial setup. To apply pending migrations later, run:

```sh
php artisan migrate
```

To add sample appointments and memories for local development or Jump testing, run:

```sh
php artisan db:seed --class=RandevuSeeder
```

The demo seeder replaces existing appointment data in the local SQLite database. Do not run it on a device with data you want to keep. The default `DatabaseSeeder` intentionally adds no records.

### Run on Android with Jump

Start the NativePHP development server:

```sh
php artisan native:jump
```

Open the NativePHP Jump app on your Android device and scan the QR code displayed in the terminal to load Randevu.

### Run the tests

```sh
php artisan test --compact
```

## Android distribution

`native:jump` is for development and testing. To create a distributable Android package, configure NativePHP Mobile signing and Android build settings, then use NativePHP's packaging workflow. This project uses Bifrost cloud builds for release packages; a local Android build requires the Android development toolchain. See the [NativePHP Mobile documentation](https://nativephp.com/docs) for packaging requirements.

There is no public app-store download link in this repository yet.
