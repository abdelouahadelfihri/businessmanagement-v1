<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- purchase_invoices ----------
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $missing = fn (string $col) => !Schema::hasColumn('purchase_invoices', $col);

            if ($missing('purchase_receipt_id'))    $table->unsignedBigInteger('purchase_receipt_id')->nullable();
            if ($missing('warehouse_id'))           $table->unsignedBigInteger('warehouse_id')->nullable();
            if ($missing('supplier_invoice_number')) $table->string('supplier_invoice_number', 100)->nullable();
            if ($missing('due_date'))               $table->date('due_date')->nullable();
            if ($missing('payment_terms'))          $table->string('payment_terms', 150)->nullable();
            if ($missing('currency'))               $table->string('currency', 3)->default('MAD');
            if ($missing('subtotal'))               $table->decimal('subtotal', 15, 2)->default(0);
            if ($missing('discount_amount'))        $table->decimal('discount_amount', 15, 2)->default(0);
            if ($missing('tax'))                    $table->decimal('tax', 15, 2)->default(0);
            if ($missing('shipping_cost'))          $table->decimal('shipping_cost', 15, 2)->default(0);
            if ($missing('total'))                  $table->decimal('total', 15, 2)->default(0);
            if ($missing('amount_paid'))            $table->decimal('amount_paid', 15, 2)->default(0);
            if ($missing('payment_status'))         $table->string('payment_status', 20)->default('unpaid');
            if ($missing('payment_method'))         $table->string('payment_method', 50)->nullable();
            if ($missing('paid_at'))                $table->date('paid_at')->nullable();
            if ($missing('notes'))                  $table->text('notes')->nullable();
            if ($missing('internal_notes'))         $table->text('internal_notes')->nullable();
            if ($missing('validated_at'))           $table->timestamp('validated_at')->nullable();
            if ($missing('created_by'))             $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            if ($missing('validated_by'))           $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        // ---------- purchase_invoice_lines ----------
        // Existing columns kept as they are: invoice_id, product_id, quantity, price, total.
        Schema::table('purchase_invoice_lines', function (Blueprint $table) {
            $missing = fn (string $col) => !Schema::hasColumn('purchase_invoice_lines', $col);

            if ($missing('purchase_order_line_id')) $table->unsignedBigInteger('purchase_order_line_id')->nullable();
            if ($missing('description'))            $table->string('description')->nullable();
            if ($missing('unit'))                   $table->string('unit', 30)->nullable();
            if ($missing('discount_percent'))       $table->decimal('discount_percent', 5, 2)->default(0);
            if ($missing('tax_rate'))               $table->decimal('tax_rate', 5, 2)->default(0);
        });
    }

    public function down(): void
    {
        foreach (['created_by', 'validated_by'] as $fk) {
            if (Schema::hasColumn('purchase_invoices', $fk)) {
                Schema::table('purchase_invoices', fn (Blueprint $t) => $t->dropConstrainedForeignId($fk));
            }
        }

        $invoiceCols = ['purchase_receipt_id', 'warehouse_id', 'supplier_invoice_number', 'due_date', 'payment_terms',
            'currency', 'discount_amount', 'shipping_cost', 'amount_paid', 'payment_status', 'payment_method',
            'paid_at', 'notes', 'internal_notes', 'validated_at'];
        $lineCols = ['purchase_order_line_id', 'description', 'unit', 'discount_percent', 'tax_rate'];

        Schema::table('purchase_invoices', function (Blueprint $table) use ($invoiceCols) {
            $table->dropColumn(array_values(array_filter($invoiceCols, fn ($c) => Schema::hasColumn('purchase_invoices', $c))));
        });
        Schema::table('purchase_invoice_lines', function (Blueprint $table) use ($lineCols) {
            $table->dropColumn(array_values(array_filter($lineCols, fn ($c) => Schema::hasColumn('purchase_invoice_lines', $c))));
        });
    }
};
