<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Замість двох булевих прапорців — два текстові бейджі з вибором значення:
        //   promo_badge:    Акція / Запитуй знижку / Уточніть вашу ціну
        //   shipping_badge: Безкоштовна доставка / Можлива безкоштовна доставка
        Schema::table('products', function (Blueprint $table) {
            $table->string('promo_badge')->nullable()->after('discount_type');
            $table->string('shipping_badge')->nullable()->after('promo_badge');
        });

        // Переносимо наявні дані: is_promo → «Акція», free_shipping → «Безкоштовна доставка».
        DB::table('products')->where('is_promo', true)->update(['promo_badge' => 'Акція']);
        DB::table('products')->where('free_shipping', true)->update(['shipping_badge' => 'Безкоштовна доставка']);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_promo', 'free_shipping']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_promo')->default(false)->after('discount_type');
            $table->boolean('free_shipping')->default(false)->after('is_promo');
        });

        DB::table('products')->where('promo_badge', 'Акція')->update(['is_promo' => true]);
        DB::table('products')->whereNotNull('shipping_badge')->update(['free_shipping' => true]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['promo_badge', 'shipping_badge']);
        });
    }
};
