<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // ✅ Polymorphic relation (SalesInvoice OR SupplierInvoice)
            $table->unsignedBigInteger('payable_id');
            $table->string('payable_type');

            // ✅ Payment info
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);

            // ✅ Optional fields
            $table->string('payment_method')->nullable(); // Cash, Bank, Check
            $table->string('reference')->nullable();

            // ✅ Timestamps
            $table->timestamps();

            // 🔥 Index for performance (VERY IMPORTANT)
            $table->index(['payable_id', 'payable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};