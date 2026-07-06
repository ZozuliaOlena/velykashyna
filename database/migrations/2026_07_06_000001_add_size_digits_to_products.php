<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Нормалізований розмір «лише цифри» для стійкого пошуку типорозміру:
 * «VF270/95R32», «270-95-32», «270 95 32», «2709532», «vf2709532» → «2709532».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('size_digits', 40)->nullable()->after('size_raw')->index();
        });

        // Бекфіл: витягуємо лише цифри з наявних розмірів.
        DB::table('products')->select('id', 'size_raw')->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $r) {
                    DB::table('products')->where('id', $r->id)->update([
                        'size_digits' => preg_replace('/\D+/', '', (string) $r->size_raw) ?: null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['size_digits']);
            $table->dropColumn('size_digits');
        });
    }
};
