<?php
// database/migrations/2024_01_01_000001_create_stock_transfers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransfersTable extends Migration
{
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->date('transfer_date');
            $table->enum('status', ['draft', 'pending', 'completed', 'cancelled'])->default('pending');
            $table->integer('total_items')->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('reference_no');
            $table->index('transfer_date');
            $table->index('status');
            $table->index('from_warehouse_id');
            $table->index('to_warehouse_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfers');
    }
}