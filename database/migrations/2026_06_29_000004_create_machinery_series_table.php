<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Серія техніки — проміжний рівень між виробником і моделлю
        // (напр. John Deere → серія «8R» → модель «8400R»).
        Schema::create('machinery_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machinery_brand_id')->constrained('machinery_brands')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index('machinery_brand_id');
        });

        Schema::table('machinery_models', function (Blueprint $table) {
            $table->foreignId('machinery_series_id')->nullable()->after('machinery_brand_id')
                ->constrained('machinery_series')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('machinery_models', function (Blueprint $table) {
            $table->dropConstrainedForeignId('machinery_series_id');
        });
        Schema::dropIfExists('machinery_series');
    }
};
