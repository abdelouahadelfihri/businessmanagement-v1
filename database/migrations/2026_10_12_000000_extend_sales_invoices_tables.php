<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $add = function (string $col, callable $def) use ($table) {
                if (!Schema::hasColumn('sales_invoices', $col)) {
                    $def($table);
                }
            };

            $add('currency', fn($t) => $t->string('currency', 3)->default('MAD'));
            $add('reference', fn($t) => $t->string('reference', 100)->nullable());
            $add('due_date', fn($t) => $t->date('due_date')->nullable());
            $add('payment_terms', fn($t) => $t->string('payment_terms', 100)->nullable());
            $add('payment_method', fn($t) => $t->string('payment_method', 50)->nullable());
            $add('billing_address', fn($t) => $t->text('billing_address')->nullable());
            $add('discount_total', fn($t) => $t->decimal('discount_total', 15, 2)->default(0));
            $add('amount_paid', fn($t) => $t->decimal('amount_paid', 15, 2)->default(0));
            $add('balance_due', fn($t) => $t->decimal('balance_due', 15, 2)->default(0));
            $add('notes', fn($t) => $t->text('notes')->nullable());
            $add('internal_notes', fn($t) => $t->text('internal_notes')->nullable());
            $add('created_by', fn($t) => $t->unsignedBigInteger('created_by')->nullable()->index());
            $add('issued_at', fn($t) => $t->timestamp('issued_at')->nullable());
            $add('paid_at', fn($t) => $t->timestamp('paid_at')->nullable());
            $add('cancelled_at', fn($t) => $t->timestamp('cancelled_at')->nullable());
            $add('cancellation_reason', fn($t) => $t->string('cancellation_reason')->nullable());
        });

        // If your `status` column is an ENUM, convert it to a string
        // (needs doctrine/dbal on Laravel < 10):
        // Schema::table('sales_invoices', fn (Blueprint $t) => $t->string('status', 20)->default('draft')->change());

        Schema::table('sales_invoice_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoice_lines', 'order_line_id')) {
                $table->unsignedBigInteger('order_line_id')->nullable()->index();
            }
            if (!Schema::hasColumn('sales_invoice_lines', 'description')) {
                $table->string('description')->nullable();
            }
            if (!Schema::hasColumn('sales_invoice_lines', 'unit')) {
                $table->string('unit', 20)->nullable();
            }
            if (!Schema::hasColumn('sales_invoice_lines', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('sales_invoice_lines', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('sales_invoice_lines', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
            }
        });

        // `discount` -> `discount_percent` (native rename needs Laravel 10+)
        if (Schema::hasColumn('sales_invoice_lines', 'discount') && !Schema::hasColumn('sales_invoice_lines', 'discount_percent')) {
            Schema::table('sales_invoice_lines', function (Blueprint $table) {
                $table->renameColumn('discount', 'discount_percent');
            });
        } elseif (!Schema::hasColumn('sales_invoice_lines', 'discount_percent')) {
            Schema::table('sales_invoice_lines', function (Blueprint $table) {
                $table->decimal('discount_percent', 5, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty (extension migration).
    }
};
