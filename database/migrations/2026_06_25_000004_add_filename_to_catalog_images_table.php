<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_images', function (Blueprint $table) {
            // Імʼя файлу-референс із імпорту (напр. "agro-710.jpg").
            // За ним масово підвантажуються самі файли і звʼязуються товари.
            $table->string('filename')->nullable()->unique()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_images', function (Blueprint $table) {
            $table->dropUnique(['filename']);
            $table->dropColumn('filename');
        });
    }
};
