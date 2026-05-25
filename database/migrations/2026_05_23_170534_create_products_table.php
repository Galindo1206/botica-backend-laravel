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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 150);

            $table->string('generic_name', 150)
                ->nullable();

            $table->string('concentration', 80)
                ->nullable();

            $table->string('pharmaceutical_form', 80)
                ->nullable();

            $table->string('presentation', 120)
                ->nullable();

            $table->string('laboratory', 120)
                ->nullable();

            $table->string('barcode', 80)
                ->nullable()
                ->unique();

            $table->string('health_registration', 80)
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->boolean('requires_prescription')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
