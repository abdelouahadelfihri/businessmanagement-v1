<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();

            // Link to purchase_orders
            $table->foreignId('purchase_order_id')
                  ->constrained('purchase_orders')
                  ->onDelete('cascade');

            // Link to products
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            // Quantity of the product in this order
            $table->decimal('quantity', 12, 2);

            // Optional: unit price and total price
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();

            $table->timestamps();

            // Indexes for faster queries
            $table->index(['purchase_order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
    }
};