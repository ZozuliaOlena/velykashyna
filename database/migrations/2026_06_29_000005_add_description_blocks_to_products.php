<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Структурований опис за фіксованим шаблоном (розділи: опис,
            // переваги, переваги над конкурентами, особливості, чому придбати).
            // З нього збирається готовий HTML у полі `description`.
            $table->json('description_blocks')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description_blocks');
        });
    }
};
