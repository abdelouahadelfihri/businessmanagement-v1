<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('cascade');

            $table->foreignId('request_id')
                ->nullable()
                ->constrained('purchase_requests')
                ->onDelete('set null');

            $table->date('order_date');
            $table->string('status');
            $table->double('total_amount', 15, 2);

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};