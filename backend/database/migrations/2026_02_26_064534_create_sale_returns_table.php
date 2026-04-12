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
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->string('reason'); // Removed the 'after' clause
            $table->decimal('refund_amount', 15, 4);
            $table->decimal('cost_price', 15, 4);
            $table->decimal('profit_reversed', 15, 4);
            $table->foreignId('processed_by')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Changed default to 'pending' for better workflow
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['sale_id', 'product_id']);
            $table->index('status');
            $table->index('processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_returns');
    }
};