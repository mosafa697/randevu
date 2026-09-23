<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            $table->smallInteger('hijri_year')->nullable();
            $table->unsignedTinyInteger('hijri_month')->nullable();
            $table->unsignedTinyInteger('hijri_day')->nullable();
            $table->string('entered_in', 16)->default('gregorian');
        });
    }

    public function down(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            $table->dropColumn(['hijri_year', 'hijri_month', 'hijri_day', 'entered_in']);
        });
    }
};
