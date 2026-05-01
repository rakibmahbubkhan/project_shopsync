<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->decimal('quantity', 15, 3);
            $table->string('type'); // sale, purchase, transfer_in, transfer_out, adjustment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            $table->index(['reference_id', 'reference_type']);
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}