<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            // Optional time-of-day: null keeps the date-only, day-precision behavior.
            $table->time('occurs_time')->nullable();
            // Off by default so existing rows keep their current phrasing.
            $table->boolean('show_hours')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            $table->dropColumn(['occurs_time', 'show_hours']);
        });
    }
};
