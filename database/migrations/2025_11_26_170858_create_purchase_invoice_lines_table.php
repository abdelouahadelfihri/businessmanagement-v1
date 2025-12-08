<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_lines', function (Blueprint $table) {
            $table->id();

            // Link to purchase_invoices.id
            $table->foreignId('invoice_id')
                  ->constrained('purchase_invoices')
                  ->onDelete('cascade');

            // Link to products.id
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            // Quantities and pricing
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_lines');
    }
};