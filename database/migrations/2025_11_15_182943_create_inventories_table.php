<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {

            // Primary key (choose bigint or int — I’ll use bigint for consistency with products)
            $table->bigIncrements('inventory_id');

            $table->integer('quantity_available');
            $table->integer('minimum_stock_level');
            $table->integer('maximum_stock_level');
            $table->integer('reorder_point');

            // Foreign Key to products.id (BIGINT UNSIGNED)
            $table->unsignedBigInteger('product_id');

            // Foreign Key to warehouses.id (INT UNSIGNED)
            $table->unsignedInteger('warehouse_id');

            // Set up foreign keys
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');

            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->onDelete('cascade');

            // Indexes (optional but useful)
            $table->index('product_id');
            $table->index('warehouse_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};