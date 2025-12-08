<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoice_lines', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Link to sales_invoices
            $table->unsignedBigInteger('sales_invoice_id');
            $table->foreign('sales_invoice_id')
                ->references('id')
                ->on('sales_invoices')
                ->onDelete('cascade');

            // Link to products
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            // Line data
            $table->double('quantity', 15, 2);
            $table->double('unit_price', 15, 2);
            $table->double('total_price', 15, 2);

            // Optional notes per line
            $table->string('notes')->nullable();

            // Indexes
            $table->index('sales_invoice_id');
            $table->index('product_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_lines');
    }
};