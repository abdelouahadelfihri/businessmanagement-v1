<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The "enhance" migrations only used the new column names when they created the
 * tables themselves; on existing tables the old names stayed. This aligns the
 * tables with the models:
 *  - sales_order_lines: sales_order_id -> order_id, unit_price -> price, total_price -> total
 *  - sales_order_lines / sales_invoice_lines: product_id nullable (free-text lines)
 *  - sales_deliveries: add warehouse_id (indexed, no FK)
 *
 * Raw ALTER ... CHANGE is used because doctrine/dbal is not installed and
 * MariaDB 10.4 has no RENAME COLUMN.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('sales_order_lines', 'sales_order_id')) {
            Schema::table('sales_order_lines', function (Blueprint $table) {
                $table->dropForeign('sales_order_lines_sales_order_id_foreign');
                $table->dropIndex('sales_order_lines_sales_order_id_index');
            });

            DB::statement('ALTER TABLE sales_order_lines CHANGE sales_order_id order_id BIGINT UNSIGNED NOT NULL');

            Schema::table('sales_order_lines', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('sales_orders')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('sales_order_lines', 'unit_price')) {
            DB::statement('ALTER TABLE sales_order_lines CHANGE unit_price price DECIMAL(15,2) NOT NULL DEFAULT 0');
        }
        if (Schema::hasColumn('sales_order_lines', 'total_price')) {
            DB::statement('ALTER TABLE sales_order_lines CHANGE total_price total DECIMAL(15,2) NOT NULL DEFAULT 0');
        }

        DB::statement('ALTER TABLE sales_order_lines MODIFY product_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE sales_invoice_lines MODIFY product_id BIGINT UNSIGNED NULL');

        if (!Schema::hasColumn('sales_deliveries', 'warehouse_id')) {
            // no FK: warehouses.id is INT UNSIGNED, not BIGINT (same as the original deliveries migration)
            Schema::table('sales_deliveries', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('sales_order_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_deliveries', 'warehouse_id')) {
            Schema::table('sales_deliveries', function (Blueprint $table) {
                $table->dropIndex(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            });
        }

        if (Schema::hasColumn('sales_order_lines', 'order_id')) {
            Schema::table('sales_order_lines', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
            });
            DB::statement('ALTER TABLE sales_order_lines CHANGE order_id sales_order_id BIGINT UNSIGNED NOT NULL');
            Schema::table('sales_order_lines', function (Blueprint $table) {
                $table->index('sales_order_id');
                $table->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();
            });
        }
        if (Schema::hasColumn('sales_order_lines', 'price')) {
            DB::statement('ALTER TABLE sales_order_lines CHANGE price unit_price DOUBLE(15,2) NOT NULL');
        }
        if (Schema::hasColumn('sales_order_lines', 'total')) {
            DB::statement('ALTER TABLE sales_order_lines CHANGE total total_price DOUBLE(15,2) NOT NULL');
        }
    }
};
