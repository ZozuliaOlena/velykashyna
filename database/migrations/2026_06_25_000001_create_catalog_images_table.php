<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Каталожне (не "живе") фото - одне зображення може належати кільком товарам.
        Schema::create('catalog_images', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable(); // підпис для зручного вибору
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('catalog_image_id')
                ->nullable()
                ->after('brand_id')
                ->constrained('catalog_images')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['catalog_image_id']);
            $table->dropColumn('catalog_image_id');
        });

        Schema::dropIfExists('catalog_images');
    }
};
