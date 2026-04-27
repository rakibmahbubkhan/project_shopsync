<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method'); // cash, card, bank_transfer, mobile_banking
            $table->string('payment_status')->default('completed'); // pending, completed, failed
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};