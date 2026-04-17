<?php
// database/migrations/2026_02_16_121841_create_purchase_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // Basic fields
            $table->decimal('quantity', 15, 2);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            
            // Discount and tax fields
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            
            // Additional fields for tracking
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('received_quantity', 15, 2)->default(0);
            $table->decimal('returned_quantity', 15, 2)->default(0);
            $table->decimal('damaged_quantity', 15, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('purchase_id');
            $table->index('product_id');
            $table->index('batch_no');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};