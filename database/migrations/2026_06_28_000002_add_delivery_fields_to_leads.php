<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('city')->nullable()->after('contact_method');
            $table->string('delivery_method')->nullable()->after('city');
            $table->string('delivery_address')->nullable()->after('delivery_method'); 
            $table->string('payment_method')->nullable()->after('delivery_address');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['city', 'delivery_method', 'delivery_address', 'payment_method']);
        });
    }
};
