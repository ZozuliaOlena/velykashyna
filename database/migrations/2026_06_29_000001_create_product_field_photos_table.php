<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Фото товару «в роботі»: встановлені/інспектовані шини на конкретній
        // техніці, з підписом (роки в роботі, мотогодини тощо).
        Schema::create('product_field_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->foreignId('machinery_type_id')->nullable()->constrained('machinery_types')->nullOnDelete();
            $table->foreignId('machinery_brand_id')->nullable()->constrained('machinery_brands')->nullOnDelete();
            $table->foreignId('machinery_model_id')->nullable()->constrained('machinery_models')->nullOnDelete();

            $table->string('caption')->nullable();   // напр. "CASE 310, 2 роки, 3000 мотогодин"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('machinery_model_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_field_photos');
    }
};
