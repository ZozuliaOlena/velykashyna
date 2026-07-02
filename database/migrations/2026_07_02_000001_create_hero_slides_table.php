<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            // Фон слайда: завантажене фото / відео або відео з YouTube.
            $table->string('bg_type')->default('image'); // image | video | youtube
            $table->string('bg_path')->nullable();        // шлях у public-диску (фото/відео)
            $table->string('youtube_url')->nullable();    // посилання на YouTube
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
