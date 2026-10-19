<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            $table->string('reminder_rule')->default('off');
            $table->string('anniversary_mode')->default('none');
            $table->unsignedTinyInteger('anniversary_lead_days')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('randevus', function (Blueprint $table) {
            $table->dropColumn(['reminder_rule', 'anniversary_mode', 'anniversary_lead_days']);
        });
    }
};
