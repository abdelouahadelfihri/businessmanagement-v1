<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates `sales_returns` / `sales_return_lines` if they do not exist yet,
 * otherwise only adds the missing columns. Safe to run on existing data.
 *
 * The models point to these two table names explicitly ($table).
 * If your existing tables are called `sale_returns` / `sale_return_lines`,
 * change the two names below AND the $table property of the two models.
 */
return new class extends Migration {
    private string $returns = 'sales_returns';
    private string $lines = 'sales_return_lines';

    public function up(): void
    {
        if (!Schema::hasTable($this->returns)) {
            Schema::create($this->returns, function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($this->lines)) {
            Schema::create($this->lines, function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        // ---- sales_returns ------------------------------------------------
        Schema::table($this->returns, function (Blueprint $table) {
            $add = function (string $col, callable $def) use ($table) {
                if (!Schema::hasColumn($this->returns, $col)) {
                    $def($table);
                }
            };

            // base columns of your original model
            $add('sales_order_id', fn($t) => $t->unsignedBigInteger('sales_order_id')->nullable()->index());
            $add('customer_id', fn($t) => $t->unsignedBigInteger('customer_id')->nullable()->index());
            $add('return_number', fn($t) => $t->string('return_number', 50)->nullable()->index());
            $add('date', fn($t) => $t->date('date')->nullable());
            $add('warehouse_id', fn($t) => $t->unsignedBigInteger('warehouse_id')->nullable()->index());
            $add('reason', fn($t) => $t->text('reason')->nullable());
            $add('subtotal', fn($t) => $t->decimal('subtotal', 15, 2)->default(0));
            $add('tax', fn($t) => $t->decimal('tax', 15, 2)->default(0));
            $add('total', fn($t) => $t->decimal('total', 15, 2)->default(0));
            $add('status', fn($t) => $t->string('status', 20)->default('draft'));

            // new columns
            $add('invoice_id', fn($t) => $t->unsignedBigInteger('invoice_id')->nullable()->index());
            $add('reference', fn($t) => $t->string('reference', 100)->nullable());
            $add('currency', fn($t) => $t->string('currency', 3)->default('MAD'));
            $add('reason_type', fn($t) => $t->string('reason_type', 40)->nullable());
            $add('refund_method', fn($t) => $t->string('refund_method', 50)->nullable());
            $add('discount_total', fn($t) => $t->decimal('discount_total', 15, 2)->default(0));
            $add('notes', fn($t) => $t->text('notes')->nullable());
            $add('internal_notes', fn($t) => $t->text('internal_notes')->nullable());
            $add('created_by', fn($t) => $t->unsignedBigInteger('created_by')->nullable()->index());
            $add('validated_at', fn($t) => $t->timestamp('validated_at')->nullable());
            $add('cancelled_at', fn($t) => $t->timestamp('cancelled_at')->nullable());
            $add('cancellation_reason', fn($t) => $t->string('cancellation_reason')->nullable());
        });

        // If your `status` column is an ENUM, convert it to a string
        // (needs doctrine/dbal on Laravel < 10):
        // Schema::table('sales_returns', fn (Blueprint $t) => $t->string('status', 20)->default('draft')->change());

        // ---- sales_return_lines -------------------------------------------
        Schema::table($this->lines, function (Blueprint $table) {
            $add = function (string $col, callable $def) use ($table) {
                if (!Schema::hasColumn($this->lines, $col)) {
                    $def($table);
                }
            };

            $add('sale_return_id', fn($t) => $t->unsignedBigInteger('sale_return_id')->nullable()->index());
            $add('product_id', fn($t) => $t->unsignedBigInteger('product_id')->nullable()->index());
            $add('quantity', fn($t) => $t->decimal('quantity', 15, 3)->default(0));
            $add('unit_price', fn($t) => $t->decimal('unit_price', 15, 2)->default(0));
            $add('subtotal', fn($t) => $t->decimal('subtotal', 15, 2)->default(0));

            $add('order_line_id', fn($t) => $t->unsignedBigInteger('order_line_id')->nullable()->index());
            $add('description', fn($t) => $t->string('description')->nullable());
            $add('unit', fn($t) => $t->string('unit', 20)->nullable());
            $add('discount_percent', fn($t) => $t->decimal('discount_percent', 5, 2)->default(0));
            $add('tax_rate', fn($t) => $t->decimal('tax_rate', 5, 2)->default(0));
            $add('tax_amount', fn($t) => $t->decimal('tax_amount', 15, 2)->default(0));
            $add('total', fn($t) => $t->decimal('total', 15, 2)->default(0));
            $add('condition', fn($t) => $t->string('condition', 20)->default('good'));
            $add('restock', fn($t) => $t->boolean('restock')->default(true));
            $add('sort_order', fn($t) => $t->unsignedInteger('sort_order')->default(0));
        });
    }

    public function down(): void
    {
        // Intentionally left empty (extension migration).
    }
};
