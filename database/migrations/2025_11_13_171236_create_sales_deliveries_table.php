<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_deliveries', function (Blueprint $table) {
            $table->id();

            // Link to sales_orders
            $table->foreignId('sales_order_id')
                  ->constrained('sales_orders')
                  ->onDelete('cascade');

            // Delivery number
            $table->string('delivery_number')->unique();

            // Delivery date
            $table->date('date');

            // Delivery status
            $table->string('status')->default('Pending');

            // Delivery total (optional)
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_deliveries');
    }
};