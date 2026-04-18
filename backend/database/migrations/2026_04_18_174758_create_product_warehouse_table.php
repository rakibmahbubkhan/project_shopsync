<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_product_warehouse_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('avg_cost', 15, 2)->default(0);
            $table->timestamps();
            
            // Prevent duplicate entries for same product in same warehouse
            $table->unique(['product_id', 'warehouse_id']);
            
            // Add indexes for better performance
            $table->index('product_id');
            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouse');
    }
};