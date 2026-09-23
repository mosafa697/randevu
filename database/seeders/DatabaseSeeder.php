<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Randevu data lives in per-device SQLite and belongs to the user,
     * so the seeder intentionally creates nothing.
     */
    public function run(): void
    {
        //
    }
}
