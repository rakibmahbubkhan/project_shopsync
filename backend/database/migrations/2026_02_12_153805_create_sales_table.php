<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'bank', 'mobile'])->default('cash');
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->date('sale_date')->index();
            $table->decimal('total_cogs', 15, 2)->default(0); // Changed to 2 decimal places
            $table->decimal('gross_profit', 15, 2)->default(0); // Changed to 2 decimal places
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); // Changed to restrictOnDelete
            $table->timestamp('created_at')->useCurrent(); // Sets current time on create
            $table->timestamp('updated_at')->nullable();
            
            // Add indexes for better performance
            $table->index(['customer_id', 'sale_date']);
            $table->index(['payment_status', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};