<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Product concerned
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            // Warehouse where the movement happens
            $table->unsignedInteger('warehouse_id');
            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->onDelete('cascade');

            // Movement type: in, out, transfer_in, transfer_out, adjustment
            $table->enum('type', [
                'in',
                'out',
                'transfer_in',
                'transfer_out',
                'adjustment'
            ]);

            // Quantity moved (+ or - depending on logic)
            $table->decimal('quantity', 10, 2);

            // Optional reason (purchase, sale, correction…)
            $table->string('reason')->nullable();

            $table->date('date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}