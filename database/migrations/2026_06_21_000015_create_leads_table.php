<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('contact_method')->nullable(); // phone/viber/telegram/form
            $table->string('status')->default('new');     // new/processing/confirmed/canceled
            $table->text('manager_comment')->nullable();
            $table->string('source')->nullable();          // cart / quick_form / call
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
