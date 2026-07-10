<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::table('products', function (Blueprint $table) {
            $table->string('condition', 20)->default('new')->after('merchant_enabled');
        });

        Schema::table('product_types', function (Blueprint $table) {
            $table->string('google_category')->nullable()->after('name');
        });

        $defaults = [
            'tire' => 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts > Motor Vehicle Tires',
            'disk' => 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts > Motor Vehicle Rims & Wheels',
        ];
        $fallback = 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts';

        foreach (DB::table('product_types')->get() as $type) {
            DB::table('product_types')
                ->where('id', $type->id)
                ->update(['google_category' => $defaults[$type->code] ?? $fallback]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
        Schema::table('product_types', function (Blueprint $table) {
            $table->dropColumn('google_category');
        });
    }
};
