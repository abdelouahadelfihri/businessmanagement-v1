<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            // Correct foreign key to customers.id
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')
                ->references('id')   // must match the column name in customers
                ->on('customers')
                ->onDelete('cascade');

            $table->string('order_number')->unique();
            $table->date('date');
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('Pending');

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};