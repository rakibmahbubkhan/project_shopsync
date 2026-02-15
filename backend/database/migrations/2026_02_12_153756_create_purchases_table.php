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
        Schema::create('purchases', function (Blueprint $table) {
        $table->id();

        $table->foreignId('supplier_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('created_by')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->decimal('total_amount', 15, 2);
        $table->enum('status', ['draft', 'confirmed'])->default('confirmed');
        $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
        $table->date('purchase_date')->index();

        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
