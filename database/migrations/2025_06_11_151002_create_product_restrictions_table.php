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
        Schema::create('product_restrictions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('product_id')->constrained('products')->references('id');
            $table->foreignId('medical_id')->constrained('medicals')->references('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_restrictions');
    }
};
