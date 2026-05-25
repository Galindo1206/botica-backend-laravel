<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('business_name', 150);

            $table->string('ruc', 20)
                ->nullable();

            $table->string('address')
                ->nullable();

            $table->string('phone', 30)
                ->nullable();

            $table->string('email', 150)
                ->nullable();

            $table->string('currency', 10)
                ->default('PEN');

            $table->decimal('tax_percentage', 5, 2)
                ->default(18);

            $table->string('logo_path')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
