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
        Schema::create('inventory_ledgers', function (Blueprint $table) {
        $table->id();

        $table->foreignId('product_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('reference_type'); // Purchase, Sale, Adjustment
        $table->unsignedBigInteger('reference_id')->nullable();

        $table->enum('movement_type', ['in', 'out']);

        $table->integer('quantity');
        $table->integer('balance_after');
        $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();


        $table->decimal('unit_cost', 15, 2)->nullable();
        $table->decimal('total_cost', 15, 2)->nullable();

        $table->foreignId('user_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_ledgers');
    }
};
