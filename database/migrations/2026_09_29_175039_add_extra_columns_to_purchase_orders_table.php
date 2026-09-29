<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('supplier_reference')->nullable();

            $table->date('expected_delivery_date')->nullable();
            $table->date('received_date')->nullable();

            $table->string('payment_status')->default('unpaid')->index();

            $table->string('currency', 3)->default('MAD');
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->string('payment_terms')->nullable();
            $table->string('shipping_method')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['cancelled_by']);

            $table->dropColumn([
                'supplier_reference', 'expected_delivery_date', 'received_date',
                'payment_status', 'currency', 'exchange_rate', 'subtotal',
                'discount_amount', 'tax_amount', 'shipping_cost', 'paid_amount',
                'payment_terms', 'shipping_method', 'delivery_address',
                'terms_conditions', 'notes', 'internal_notes',
                'created_by', 'approved_by', 'approved_at', 'sent_at',
                'cancelled_by', 'cancelled_at', 'cancellation_reason',
                'deleted_at',
            ]);
        });
    }
};