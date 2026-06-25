<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Знижка вже є (discount_value/discount_type). Тут — два прапорці:
            $table->boolean('is_promo')->default(false)->after('discount_type');       // бейдж «Акція»
            $table->boolean('free_shipping')->default(false)->after('is_promo');        // безкоштовна доставка
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_promo', 'free_shipping']);
        });
    }
};
