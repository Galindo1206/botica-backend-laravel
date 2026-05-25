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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('batch_id')
                ->nullable()
                ->constrained('batches')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('movement_type', 30);

            $table->string('reason', 150)
                ->nullable();

            $table->integer('quantity');

            $table->integer('previous_stock')
                ->nullable();

            $table->integer('new_stock')
                ->nullable();

            $table->string('reference_type', 50)
                ->nullable();

            $table->unsignedBigInteger('reference_id')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamp('movement_date')
                ->useCurrent();

            $table->timestamps();

            $table->index('movement_type');
            $table->index('movement_date');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
