<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            
            $table->boolean('is_promo')->default(false)->after('discount_type');       
            $table->boolean('free_shipping')->default(false)->after('is_promo');        
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_promo', 'free_shipping']);
        });
    }
};
