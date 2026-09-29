<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('po_number')->unique();
            $table->string('supplier_reference')->nullable();   // supplier's quote/proforma number
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();

            // Dates
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('received_date')->nullable();

            // Status
            $table->string('status')->default('draft')->index();
            // draft, pending_approval, approved, sent, partially_received, received, closed, cancelled
            $table->string('payment_status')->default('unpaid')->index(); // unpaid, partial, paid

            // Money
            $table->string('currency', 3)->default('MAD');
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);       // TVA
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);

            // Terms & delivery
            $table->string('payment_terms')->nullable();            // e.g. "Net 30", "60 days end of month"
            $table->string('shipping_method')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();                      // visible to supplier
            $table->text('internal_notes')->nullable();             // internal only

            // Workflow / audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            //
        });
    }
};
