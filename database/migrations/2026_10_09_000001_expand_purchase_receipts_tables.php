<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- purchase_receipts ----------
        Schema::table('purchase_receipts', function (Blueprint $table) {
            $missing = fn (string $col) => !Schema::hasColumn('purchase_receipts', $col);

            if ($missing('delivery_note_number')) $table->string('delivery_note_number', 100)->nullable();
            if ($missing('carrier'))              $table->string('carrier', 150)->nullable();
            if ($missing('tracking_number'))      $table->string('tracking_number', 150)->nullable();
            if ($missing('currency'))             $table->string('currency', 3)->default('MAD');
            if ($missing('quality_status'))       $table->string('quality_status', 20)->default('pending');
            if ($missing('subtotal'))             $table->decimal('subtotal', 15, 2)->default(0);
            if ($missing('discount_amount'))      $table->decimal('discount_amount', 15, 2)->default(0);
            if ($missing('tax_amount'))           $table->decimal('tax_amount', 15, 2)->default(0);
            if ($missing('shipping_cost'))        $table->decimal('shipping_cost', 15, 2)->default(0);
            if ($missing('notes'))                $table->text('notes')->nullable();
            if ($missing('internal_notes'))       $table->text('internal_notes')->nullable();
            if ($missing('validated_at'))         $table->timestamp('validated_at')->nullable();
            if ($missing('received_by'))          $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            if ($missing('validated_by'))         $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        // If the old lines table used "quantity", keep its data under the new name (Laravel 10+).
        if (Schema::hasColumn('purchase_receipt_lines', 'quantity') && !Schema::hasColumn('purchase_receipt_lines', 'received_quantity')) {
            Schema::table('purchase_receipt_lines', fn (Blueprint $t) => $t->renameColumn('quantity', 'received_quantity'));
        }

        // ---------- purchase_receipt_lines ----------
        Schema::table('purchase_receipt_lines', function (Blueprint $table) {
            $missing = fn (string $col) => !Schema::hasColumn('purchase_receipt_lines', $col);

            if ($missing('purchase_order_line_id')) $table->unsignedBigInteger('purchase_order_line_id')->nullable();
            if ($missing('product_id'))             $table->unsignedBigInteger('product_id')->nullable();
            if ($missing('description'))            $table->string('description')->nullable();
            if ($missing('unit'))                   $table->string('unit', 30)->nullable();
            if ($missing('ordered_quantity'))       $table->decimal('ordered_quantity', 15, 3)->default(0);
            if ($missing('received_quantity'))      $table->decimal('received_quantity', 15, 3)->default(0);
            if ($missing('rejected_quantity'))      $table->decimal('rejected_quantity', 15, 3)->default(0);
            if ($missing('unit_price'))             $table->decimal('unit_price', 15, 2)->default(0);
            if ($missing('discount_percent'))       $table->decimal('discount_percent', 5, 2)->default(0);
            if ($missing('tax_rate'))               $table->decimal('tax_rate', 5, 2)->default(0);
            if ($missing('total_price'))            $table->decimal('total_price', 15, 2)->default(0);
            if ($missing('batch_number'))           $table->string('batch_number', 100)->nullable();
            if ($missing('expiry_date'))            $table->date('expiry_date')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['received_by', 'validated_by'] as $fk) {
            if (Schema::hasColumn('purchase_receipts', $fk)) {
                Schema::table('purchase_receipts', fn (Blueprint $t) => $t->dropConstrainedForeignId($fk));
            }
        }

        $receiptCols = ['delivery_note_number', 'carrier', 'tracking_number', 'currency', 'quality_status', 'subtotal',
            'discount_amount', 'tax_amount', 'shipping_cost', 'notes', 'internal_notes', 'validated_at'];
        $lineCols = ['purchase_order_line_id', 'description', 'unit', 'ordered_quantity', 'rejected_quantity',
            'unit_price', 'discount_percent', 'tax_rate', 'total_price', 'batch_number', 'expiry_date'];

        Schema::table('purchase_receipts', function (Blueprint $table) use ($receiptCols) {
            $table->dropColumn(array_values(array_filter($receiptCols, fn ($c) => Schema::hasColumn('purchase_receipts', $c))));
        });
        Schema::table('purchase_receipt_lines', function (Blueprint $table) use ($lineCols) {
            $table->dropColumn(array_values(array_filter($lineCols, fn ($c) => Schema::hasColumn('purchase_receipt_lines', $c))));
        });
    }
};
