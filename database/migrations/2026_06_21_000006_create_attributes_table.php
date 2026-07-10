<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->nullable()
                ->constrained()->nullOnDelete(); 
            $table->string('code');
            $table->string('name');
            $table->enum('data_type', ['text', 'number', 'select', 'boolean']);
            $table->string('unit')->nullable();
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_type_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
