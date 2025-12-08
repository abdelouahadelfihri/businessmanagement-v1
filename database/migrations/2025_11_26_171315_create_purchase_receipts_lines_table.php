<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipt_lines', function (Blueprint $table) {
            $table->id();

            // Link to the receipt header
            $table->foreignId('receipt_id')
                ->constrained('purchase_receipts')
                ->onDelete('cascade');

            // Link to the product
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            // FIX: warehouse_id MUST be unsignedInteger() because warehouses.id uses increments()
            $table->unsignedInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('set null');

            // Line data
            $table->integer('quantity_received');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_lines');
    }
};