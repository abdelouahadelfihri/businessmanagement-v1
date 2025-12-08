<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('product_id');

            $table->double('quantity', 15, 2);
            $table->double('unit_price', 15, 2);
            $table->double('total_price', 15, 2);

            // Foreign keys
            $table->foreign('sales_order_id')
                  ->references('id')
                  ->on('sales_orders')
                  ->onDelete('cascade');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');

            // Indexes
            $table->index('sales_order_id');
            $table->index('product_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_lines');
    }
};