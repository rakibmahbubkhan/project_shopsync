<?php
// database/migrations/2026_02_16_121840_create_purchases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->date('purchase_date');
            $table->string('reference_no')->unique();
            
            // Amount fields
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            
            // Status fields
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->enum('status', ['ordered', 'received', 'pending'])->default('pending');
            
            // Additional fields
            $table->text('notes')->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('delivered_date')->nullable();
            
            // Tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Indexes
            $table->index('purchase_date');
            $table->index('payment_status');
            $table->index('status');
            $table->index('reference_no');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};