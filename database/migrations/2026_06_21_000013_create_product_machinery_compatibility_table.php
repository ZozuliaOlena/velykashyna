<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_machinery_compatibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machinery_type_id')->constrained();
            $table->foreignId('machinery_brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machinery_model_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()
                ->constrained('machinery_positions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'machinery_type_id'], 'prod_mach_compat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_machinery_compatibility');
    }
};
