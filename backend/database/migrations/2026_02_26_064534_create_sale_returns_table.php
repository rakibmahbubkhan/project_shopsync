<?php
// database/migrations/xxxx_xx_xx_update_sale_returns_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists, if not create it
        if (!Schema::hasTable('sale_returns')) {
            Schema::create('sale_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('return_date');
                $table->string('reason')->nullable();
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->enum('status', ['pending', 'approved', 'completed', 'rejected'])->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        } else {
            // Add missing columns if table exists
            Schema::table('sale_returns', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_returns', 'notes')) {
                    $table->text('notes')->nullable()->after('reason');
                }
                if (!Schema::hasColumn('sale_returns', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('sale_returns', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
            });
        }

        // Create sale_return_items table
        if (!Schema::hasTable('sale_return_items')) {
            Schema::create('sale_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained('sale_returns')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('selling_price', 15, 2);
                $table->decimal('cost_price', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // Create refunds table
        if (!Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained('sale_returns')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->string('payment_method');
                $table->string('reference_number')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};