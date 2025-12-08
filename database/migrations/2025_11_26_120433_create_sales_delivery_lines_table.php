<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_delivery_lines', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Link to sales_deliveries
            $table->unsignedBigInteger('sales_delivery_id');
            $table->foreign('sales_delivery_id')
                  ->references('id')
                  ->on('sales_deliveries')
                  ->onDelete('cascade');

            // Product reference
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');

            // Quantity and prices
            $table->double('quantity', 15, 2);
            $table->double('unit_price', 15, 2)->nullable();
            $table->double('total_price', 15, 2)->nullable();

            // Optional: add notes
            $table->string('notes')->nullable();

            // Indexes for faster lookups
            $table->index('sales_delivery_id');
            $table->index('product_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_delivery_lines');
    }
};