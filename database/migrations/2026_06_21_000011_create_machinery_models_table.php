<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machinery_brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machinery_type_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // 8400, MX230, Steiger...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_models');
    }
};
