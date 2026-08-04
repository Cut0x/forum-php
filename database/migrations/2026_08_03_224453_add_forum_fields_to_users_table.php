<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->unique()->after('name');
            $table->string('role', 20)->default('member')->after('email');
            $table->text('bio')->nullable()->after('password');
            $table->string('avatar')->nullable()->after('bio');
            $table->boolean('notifications_enabled')->default(true)->after('avatar');
            $table->timestamp('suspended_until')->nullable()->after('notifications_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'bio', 'avatar', 'notifications_enabled', 'suspended_until']);
        });
    }
};
