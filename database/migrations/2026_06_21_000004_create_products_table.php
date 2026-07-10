<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();                 
            $table->foreignId('product_type_id')->constrained();
            $table->string('name');                          
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model')->nullable();              

            $table->string('size_raw')->nullable();           
            $table->decimal('size_width', 7, 2)->nullable();  
            $table->decimal('size_profile', 7, 2)->nullable();
            $table->decimal('rim_diameter', 7, 2)->nullable();

            $table->string('rd_type', 1)->nullable();         
            $table->string('tube_type', 2)->nullable();       
            $table->string('ply_rating', 10)->nullable();     
            $table->string('load_speed_index', 30)->nullable(); 
            $table->string('specification')->nullable();      

            $table->enum('stock_status', ['in_stock', 'on_order', 'inquiry'])
                ->default('inquiry');

            $table->enum('price_mode', ['fixed', 'from', 'inquiry'])
                ->default('inquiry');
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('UAH');
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->enum('discount_type', ['percent', 'amount'])->nullable();

            $table->boolean('merchant_enabled')->default(false);

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_h1')->nullable();
            $table->string('slug')->unique();                 

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['size_width', 'size_profile', 'rim_diameter'], 'idx_size_filter');
            $table->index('stock_status');
            $table->index('merchant_enabled');
        });

        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE products ADD FULLTEXT idx_products_search (sku, name, size_raw)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
