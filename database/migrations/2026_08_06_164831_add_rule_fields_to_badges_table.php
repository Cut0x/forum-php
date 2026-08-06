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
        Schema::table('badges', function (Blueprint $table) {
            // null ou 'manual' = attribution manuelle uniquement (depuis /admin/users).
            $table->string('rule_type')->nullable()->after('color');
            // Seuil (nombre) pour posts_count/topics_count/account_age_days, nom de rôle pour role.
            $table->string('rule_value')->nullable()->after('rule_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['rule_type', 'rule_value']);
        });
    }
};
