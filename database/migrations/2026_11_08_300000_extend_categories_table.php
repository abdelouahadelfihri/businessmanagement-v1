<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns categories into a small hierarchy with display settings:
 * code (auto CAT-001), parent category, colour, icon, default VAT rate
 * applied to new products, sort order and an active flag.
 */
return new class extends Migration {
    public function up(): void
    {
        // Guarded so the migration can be re-run after a partial failure
        // (MySQL DDL is not transactional). `name` is already unique.
        $has = fn(string $column) => Schema::hasColumn('categories', $column);

        Schema::table('categories', function (Blueprint $table) use ($has) {
            if (! $has('code')) {
                $table->string('code', 20)->nullable()->unique()->after('id');
            }
            if (! $has('parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('code')
                    ->constrained('categories')->nullOnDelete();
            }
            if (! $has('color')) {
                $table->string('color', 7)->default('#0d6efd')->after('description');
            }
            if (! $has('icon')) {
                $table->string('icon', 50)->default('bi-tag')->after('color');
            }
            if (! $has('default_tax_rate')) {
                $table->decimal('default_tax_rate', 5, 2)->nullable()->after('icon');
            }
            if (! $has('sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('default_tax_rate');
            }
            if (! $has('is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        DB::table('categories')->whereNull('code')->orderBy('id')->get(['id'])->each(function ($row) {
            DB::table('categories')->where('id', $row->id)->update(['code' => sprintf('CAT-%03d', $row->id)]);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'parent_id', 'color', 'icon', 'default_tax_rate', 'sort_order', 'is_active']);
        });
    }
};
