<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            $table->boolean('show_years')->default(true);
            $table->boolean('show_months')->default(true);
            $table->boolean('show_days')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            $table->dropColumn(['show_years', 'show_months', 'show_days']);
        });
    }
};
