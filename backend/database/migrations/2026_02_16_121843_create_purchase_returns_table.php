<?php
// database/migrations/xxxx_xx_xx_create_purchase_returns_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchase Returns table
        if (!Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('return_date');
                $table->string('reason');
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->enum('status', ['pending', 'approved', 'completed', 'rejected'])->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        // Purchase Return Items table
        if (!Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained('purchase_returns')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('purchase_price', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // Supplier Credits table (for tracking money owed back)
        if (!Schema::hasTable('supplier_credits')) {
            Schema::create('supplier_credits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained('purchase_returns')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['pending', 'settled', 'used'])->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_credits');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};