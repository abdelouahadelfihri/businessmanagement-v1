<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Same issue as the sales side: the "expand" migrations only used the new column
 * names when they created the tables themselves. This aligns the existing purchase
 * tables with the models / controllers / views:
 *  - purchase_orders: total_amount defaults to 0 (the order is saved before its lines)
 *  - purchase_receipts: order_id -> purchase_order_id, receipt_date -> date,
 *    add warehouse_id (indexed, no FK) and total
 *  - purchase_receipt_lines: receipt_id -> purchase_receipt_id, product_id nullable,
 *    old quantity_received (unused, replaced by received_quantity) nullable
 *  - purchase_invoices: order_id -> purchase_order_id (nullable: direct invoices),
 *    add supplier_id and status
 *  - purchase_invoice_lines: unit_price -> price, line_total -> total, product_id nullable
 *
 * Raw ALTER ... CHANGE is used because doctrine/dbal is not installed and
 * MariaDB 10.4 has no RENAME COLUMN.
 */
return new class extends Migration {
    public function up(): void
    {
        // ---------- purchase_orders ----------
        // The order is inserted before its lines; recalculateTotals() fills total_amount afterwards.
        DB::statement('ALTER TABLE purchase_orders MODIFY total_amount DOUBLE(15,2) NOT NULL DEFAULT 0');

        // ---------- purchase_receipts ----------
        if (Schema::hasColumn('purchase_receipts', 'order_id')) {
            Schema::table('purchase_receipts', fn (Blueprint $t) => $t->dropForeign('purchase_receipts_order_id_foreign'));
            DB::statement('ALTER TABLE purchase_receipts CHANGE order_id purchase_order_id BIGINT UNSIGNED NOT NULL');
            Schema::table('purchase_receipts', function (Blueprint $table) {
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            });
        }
        if (Schema::hasColumn('purchase_receipts', 'receipt_date')) {
            DB::statement('ALTER TABLE purchase_receipts CHANGE receipt_date `date` DATE NOT NULL');
        }
        Schema::table('purchase_receipts', function (Blueprint $table) {
            // no FK: warehouses.id is INT UNSIGNED, not BIGINT (same as sales_deliveries / purchase_invoices)
            if (!Schema::hasColumn('purchase_receipts', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('supplier_id')->index();
            }
            if (!Schema::hasColumn('purchase_receipts', 'total')) {
                $table->decimal('total', 15, 2)->default(0)->after('shipping_cost');
            }
        });

        // ---------- purchase_receipt_lines ----------
        if (Schema::hasColumn('purchase_receipt_lines', 'receipt_id')) {
            Schema::table('purchase_receipt_lines', fn (Blueprint $t) => $t->dropForeign('purchase_receipt_lines_receipt_id_foreign'));
            DB::statement('ALTER TABLE purchase_receipt_lines CHANGE receipt_id purchase_receipt_id BIGINT UNSIGNED NOT NULL');
            Schema::table('purchase_receipt_lines', function (Blueprint $table) {
                $table->foreign('purchase_receipt_id')->references('id')->on('purchase_receipts')->cascadeOnDelete();
            });
        }
        DB::statement('ALTER TABLE purchase_receipt_lines MODIFY product_id BIGINT UNSIGNED NULL');
        if (Schema::hasColumn('purchase_receipt_lines', 'quantity_received')) {
            DB::statement('ALTER TABLE purchase_receipt_lines MODIFY quantity_received INT NULL');
        }

        // ---------- purchase_invoices ----------
        if (Schema::hasColumn('purchase_invoices', 'order_id')) {
            Schema::table('purchase_invoices', fn (Blueprint $t) => $t->dropForeign('purchase_invoices_order_id_foreign'));
            DB::statement('ALTER TABLE purchase_invoices CHANGE order_id purchase_order_id BIGINT UNSIGNED NULL');
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            });
        }
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('purchase_receipt_id')->constrained('suppliers')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_invoices', 'status')) {
                $table->string('status', 20)->default('draft')->after('total')->index();
            }
        });

        // ---------- purchase_invoice_lines ----------
        if (Schema::hasColumn('purchase_invoice_lines', 'unit_price')) {
            DB::statement('ALTER TABLE purchase_invoice_lines CHANGE unit_price price DECIMAL(15,2) NOT NULL DEFAULT 0');
        }
        if (Schema::hasColumn('purchase_invoice_lines', 'line_total')) {
            DB::statement('ALTER TABLE purchase_invoice_lines CHANGE line_total total DECIMAL(15,2) NOT NULL DEFAULT 0');
        }
        DB::statement('ALTER TABLE purchase_invoice_lines MODIFY product_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // ---------- purchase_invoice_lines ----------
        if (Schema::hasColumn('purchase_invoice_lines', 'price')) {
            DB::statement('ALTER TABLE purchase_invoice_lines CHANGE price unit_price DECIMAL(15,2) NOT NULL');
        }
        if (Schema::hasColumn('purchase_invoice_lines', 'total')) {
            DB::statement('ALTER TABLE purchase_invoice_lines CHANGE total line_total DECIMAL(15,2) NOT NULL');
        }

        // ---------- purchase_invoices ----------
        if (Schema::hasColumn('purchase_invoices', 'status')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
        if (Schema::hasColumn('purchase_invoices', 'supplier_id')) {
            Schema::table('purchase_invoices', fn (Blueprint $t) => $t->dropConstrainedForeignId('supplier_id'));
        }
        if (Schema::hasColumn('purchase_invoices', 'purchase_order_id')) {
            Schema::table('purchase_invoices', fn (Blueprint $t) => $t->dropForeign(['purchase_order_id']));
            DB::statement('ALTER TABLE purchase_invoices CHANGE purchase_order_id order_id BIGINT UNSIGNED NOT NULL');
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            });
        }

        // ---------- purchase_receipt_lines ----------
        if (Schema::hasColumn('purchase_receipt_lines', 'purchase_receipt_id')) {
            Schema::table('purchase_receipt_lines', fn (Blueprint $t) => $t->dropForeign(['purchase_receipt_id']));
            DB::statement('ALTER TABLE purchase_receipt_lines CHANGE purchase_receipt_id receipt_id BIGINT UNSIGNED NOT NULL');
            Schema::table('purchase_receipt_lines', function (Blueprint $table) {
                $table->foreign('receipt_id')->references('id')->on('purchase_receipts')->cascadeOnDelete();
            });
        }

        // ---------- purchase_receipts ----------
        Schema::table('purchase_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_receipts', 'warehouse_id')) {
                $table->dropIndex(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
            if (Schema::hasColumn('purchase_receipts', 'total')) {
                $table->dropColumn('total');
            }
        });
        if (Schema::hasColumn('purchase_receipts', 'date')) {
            DB::statement('ALTER TABLE purchase_receipts CHANGE `date` receipt_date DATE NOT NULL');
        }
        if (Schema::hasColumn('purchase_receipts', 'purchase_order_id')) {
            Schema::table('purchase_receipts', fn (Blueprint $t) => $t->dropForeign(['purchase_order_id']));
            DB::statement('ALTER TABLE purchase_receipts CHANGE purchase_order_id order_id BIGINT UNSIGNED NOT NULL');
            Schema::table('purchase_receipts', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            });
        }

        // ---------- purchase_orders ----------
        DB::statement('ALTER TABLE purchase_orders MODIFY total_amount DOUBLE(15,2) NOT NULL');
    }
};
