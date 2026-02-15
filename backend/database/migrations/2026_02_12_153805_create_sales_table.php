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
        Schema::create('sales', function (Blueprint $table) {
        $table->id();

        $table->foreignId('customer_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->foreignId('created_by')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->decimal('total_amount', 15, 2);
        $table->decimal('discount', 15, 2)->default(0);
        $table->decimal('tax', 15, 2)->default(0);

        $table->enum('payment_method', ['cash', 'card', 'bank', 'mobile'])
            ->default('cash');

        $table->enum('payment_status', ['pending', 'partial', 'paid'])
            ->default('pending');

        $table->date('sale_date')->index();

        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
