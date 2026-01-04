<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id(); // primary key
            $table->unsignedInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(0);

            $table->foreign('warehouse_id')
                ->references('id')->on('warehouses')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            $table->unique(['warehouse_id', 'product_id']); // only one row per product per warehouse

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};