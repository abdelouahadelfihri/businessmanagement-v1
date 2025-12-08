<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();

            // Link to purchase order
            $table->foreignId('order_id')
                ->constrained('purchase_orders')
                ->onDelete('cascade');

            // Link to supplier
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->onDelete('cascade');

            $table->string('receipt_number')->unique();
            $table->date('receipt_date');
            $table->string('status')->default('Pending'); // Pending / Completed

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};