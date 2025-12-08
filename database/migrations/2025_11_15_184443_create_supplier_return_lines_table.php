<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_return_lines', function (Blueprint $table) {
            $table->id();

            // Reference sales_returns.id
            $table->foreignId('supplier_return_id')
                  ->constrained('supplier_returns', 'id')
                  ->onDelete('cascade');

            // Reference products.id (BIGINT matches default product PK if you used $table->id())
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            // Reference warehouses.id (INT UNSIGNED matches parent)
            $table->unsignedInteger('warehouse_id');
            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->onDelete('cascade');

            $table->decimal('quantity', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_return_lines');
    }
};