<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('reference_type'); // purchase, sale, adjustment, transfer
            $table->unsignedBigInteger('reference_id');
            $table->enum('type', ['in', 'out']);
            $table->decimal('quantity', 15, 2);
            $table->decimal('old_quantity', 15, 2)->default(0);
            $table->decimal('new_quantity', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['reference_type', 'reference_id']);
            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};