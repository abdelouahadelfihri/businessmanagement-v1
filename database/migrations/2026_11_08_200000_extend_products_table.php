<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the products table into a full product master record.
 *
 *  - category -> category_id and unit -> unit_id (the model relations expect the
 *    *_id names), both nullable, and deleting a category / unit no longer deletes
 *    its products (FKs become ON DELETE SET NULL)
 *  - code becomes unique and optional (auto PRD-00001), bar_code optional + unique
 *  - adds type, description, brand, prices (purchase / sale), VAT rate,
 *    stock settings (track stock, max stock), preferred supplier, weight, notes
 *
 * Raw ALTER ... CHANGE is used because doctrine/dbal is not installed and
 * MariaDB 10.4 has no RENAME COLUMN.
 */
return new class extends Migration {
    public function up(): void
    {
        // ---- category / unit -> category_id / unit_id ----
        if (Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign('products_category_foreign');
                $table->dropForeign('products_unit_foreign');
                $table->dropIndex('products_category_index');
                $table->dropIndex('products_unit_index');
            });

            DB::statement('ALTER TABLE products CHANGE category category_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE products CHANGE unit unit_id BIGINT UNSIGNED NULL');

            Schema::table('products', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
                $table->foreign('unit_id')->references('unit_id')->on('units')->nullOnDelete();
            });
        }

        // ---- code / bar_code ----
        DB::statement('ALTER TABLE products MODIFY code VARCHAR(50) NULL');
        DB::statement('ALTER TABLE products MODIFY bar_code VARCHAR(100) NULL');
        DB::table('products')->where('bar_code', '')->update(['bar_code' => null]);

        Schema::table('products', function (Blueprint $table) {
            $table->unique('code');
            $table->unique('bar_code');

            $table->enum('type', ['stockable', 'consumable', 'service'])->default('stockable')->after('bar_code');
            $table->text('description')->nullable()->after('name');
            $table->string('brand', 100)->nullable()->after('description');

            // Pricing
            $table->decimal('purchase_price', 15, 2)->default(0)->after('unit_id');
            $table->decimal('sale_price', 15, 2)->default(0)->after('purchase_price');
            $table->decimal('tax_rate', 5, 2)->default(20)->after('sale_price');

            // Stock
            $table->boolean('track_stock')->default(true)->after('tax_rate');
            $table->unsignedInteger('max_stock')->nullable()->after('reorder_level');

            // Purchasing
            $table->foreignId('preferred_supplier_id')->nullable()->after('max_stock')
                ->constrained('suppliers')->nullOnDelete();

            // Misc
            $table->decimal('weight', 10, 3)->nullable()->after('preferred_supplier_id'); // kg
            $table->text('notes')->nullable()->after('weight');

            $table->index('name');
            $table->index('is_active');
        });

        // Codes for any existing rows
        DB::table('products')->whereNull('code')->orWhere('code', '')->orderBy('id')->get(['id'])->each(function ($row) {
            DB::table('products')->where('id', $row->id)->update(['code' => sprintf('PRD-%05d', $row->id)]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['preferred_supplier_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['is_active']);
            $table->dropUnique(['code']);
            $table->dropUnique(['bar_code']);
            $table->dropColumn([
                'type', 'description', 'brand', 'purchase_price', 'sale_price', 'tax_rate',
                'track_stock', 'max_stock', 'preferred_supplier_id', 'weight', 'notes',
            ]);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['unit_id']);
        });

        DB::statement('ALTER TABLE products CHANGE category_id category BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE products CHANGE unit_id unit BIGINT UNSIGNED NOT NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category')->references('id')->on('categories')->cascadeOnDelete();
            $table->foreign('unit')->references('unit_id')->on('units')->cascadeOnDelete();
        });
    }
};
