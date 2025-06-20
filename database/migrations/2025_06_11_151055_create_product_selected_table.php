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
        Schema::create('product_selected', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->enum('status',['accepted','rejected']);
            $table->foreignId('product_id')->constrained('products')->references('id');
            $table->foreignId('seller_id')->constrained('sellers')->references('id');
            $table->foreignId('student_id')->constrained('students')->references('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_selected');
    }
};
