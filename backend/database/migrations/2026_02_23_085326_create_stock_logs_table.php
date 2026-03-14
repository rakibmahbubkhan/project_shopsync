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
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // Add warehouse_id column
            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('reference_type', ['purchase', 'sale', 'adjustment']);

            $table->unsignedBigInteger('reference_id')->nullable();

            $table->enum('type', ['in', 'out', 'adjustment']);

            $table->integer('quantity');

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['product_id', 'reference_type']);
            
            // Unique constraint - moved inside the create table
            $table->unique(['product_id', 'warehouse_id']);
        });

        // Remove this separate Schema::table call since we've moved the unique constraint inside
        // Schema::table('stock_logs', function (Blueprint $table) {
        //     $table->unique(['product_id', 'warehouse_id']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};