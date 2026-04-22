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
        Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable()->unique();
        $table->string('contact_person')->nullable();
        $table->string('website')->nullable();
        $table->string('mobile_number')->nullable();
        $table->string('phone_number')->nullable(); // existing was 'phone'
        $table->string('tax_number')->nullable();
        $table->text('billing_address')->nullable();
        $table->string('billing_country')->nullable();
        $table->string('billing_city')->nullable();
        $table->text('shipping_address')->nullable();
        $table->string('shipping_country')->nullable();
        $table->string('shipping_city')->nullable();
        $table->text('description')->nullable();
        $table->string('logo')->nullable();
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
