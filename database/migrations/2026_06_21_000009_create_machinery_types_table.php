<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Трактор, Комбайн, Сівалка...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_types');
    }
};
