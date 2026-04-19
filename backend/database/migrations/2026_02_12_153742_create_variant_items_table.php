<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('variant_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
            
            $table->unique(['variant_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('variant_items');
    }
};