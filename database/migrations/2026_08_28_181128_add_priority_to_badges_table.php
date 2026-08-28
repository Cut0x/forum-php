<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            // Détermine le badge "le plus important" d'un utilisateur (affiché seul à côté
            // de son pseudo sur les messages) : le plus élevé l'emporte. Réglable en admin.
            $table->unsignedInteger('priority')->default(0)->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
