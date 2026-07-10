<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Індекси для пришвидшення адмінки - без зміни функціоналу.
 *   leads.status  - лічильник «нових» заявок у шапці (кожне завантаження)
 *                   + фільтр за статусом.
 *   leads.source  - фільтр «Тип» (Кошик / Консультація / Вручну).
 *   products.size_raw - точний фільтр за типорозміром і distinct-список.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index('status');
            $table->index('source');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('size_raw');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_status_index');
            $table->dropIndex('leads_source_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_size_raw_index');
        });
    }
};
