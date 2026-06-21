<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2FA-поля (two_factor_secret, two_factor_recovery_codes,
        // two_factor_confirmed_at) додає окрема міграція Laravel Fortify
        // після composer require laravel/fortify && php artisan fortify:install
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('manager')->after('email'); // admin / manager
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
