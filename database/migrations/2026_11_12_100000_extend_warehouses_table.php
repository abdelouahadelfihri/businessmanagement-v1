<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns warehouses into a full storage-site record:
 *  - location_owner_id -> location_id (nullable, set null when the location is deleted)
 *  - code (auto WH-001), type, default flag, manager & contact details,
 *    own address, capacity, temperature range for refrigerated sites,
 *    notes and an active flag; name becomes unique.
 *
 * Raw ALTER ... CHANGE is used because doctrine/dbal is not installed.
 */
return new class extends Migration {
    public function up(): void
    {
        // Every step is guarded so the migration can be re-run after a partial failure
        // (MySQL DDL is not transactional). locations.location_id is INT UNSIGNED.
        $indexes = fn() => collect(DB::select('SHOW INDEX FROM warehouses'))->pluck('Key_name')->unique()->all();
        $foreignKeys = fn() => collect(DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'warehouses' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        ))->pluck('CONSTRAINT_NAME')->all();

        if (in_array('warehouses_location_owner_id_foreign', $foreignKeys(), true)) {
            DB::statement('ALTER TABLE warehouses DROP FOREIGN KEY warehouses_location_owner_id_foreign');
        }
        if (Schema::hasColumn('warehouses', 'location_owner_id')) {
            DB::statement('ALTER TABLE warehouses CHANGE location_owner_id location_id INT UNSIGNED NULL');
        } else {
            DB::statement('ALTER TABLE warehouses MODIFY location_id INT UNSIGNED NULL');
        }
        if (in_array('warehouses_location_owner_id_index', $indexes(), true)) {
            DB::statement('ALTER TABLE warehouses DROP INDEX warehouses_location_owner_id_index');
        }
        if (! in_array('warehouses_location_id_foreign', $foreignKeys(), true)) {
            Schema::table('warehouses', function (Blueprint $t) {
                $t->foreign('location_id')->references('location_id')->on('locations')->nullOnDelete();
            });
        }

        $has = fn(string $column) => Schema::hasColumn('warehouses', $column);

        Schema::table('warehouses', function (Blueprint $table) use ($has) {
            if (! $has('code')) {
                $table->string('code', 20)->nullable()->unique()->after('id');
            }
            if (! $has('type')) {
                $table->enum('type', ['main', 'secondary', 'store', 'transit', 'returns'])->default('main')->after('name');
            }
            if (! $has('is_default')) {
                $table->boolean('is_default')->default(false)->after('type');
            }
            if (! $has('manager_name')) {
                $table->string('manager_name', 100)->nullable()->after('location_id');
            }
            if (! $has('phone')) {
                $table->string('phone', 30)->nullable()->after('manager_name');
            }
            if (! $has('email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (! $has('address')) {
                $table->text('address')->nullable()->after('email');
            }
            if (! $has('capacity_m2')) {
                $table->decimal('capacity_m2', 10, 2)->nullable()->after('address');
            }
            if (! $has('min_temperature')) {
                $table->decimal('min_temperature', 5, 1)->nullable()->after('is_refrigerated');
            }
            if (! $has('max_temperature')) {
                $table->decimal('max_temperature', 5, 1)->nullable()->after('min_temperature');
            }
            if (! $has('notes')) {
                $table->text('notes')->nullable()->after('max_temperature');
            }
            if (! $has('is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
        });

        if (! in_array('warehouses_name_unique', $indexes(), true)) {
            Schema::table('warehouses', fn(Blueprint $t) => $t->unique('name'));
        }

        DB::table('warehouses')->whereNull('code')->orderBy('id')->get(['id'])->each(function ($row) {
            DB::table('warehouses')->where('id', $row->id)->update(['code' => sprintf('WH-%03d', $row->id)]);
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code', 'type', 'is_default', 'manager_name', 'phone', 'email', 'address',
                'capacity_m2', 'min_temperature', 'max_temperature', 'notes', 'is_active',
            ]);
            $table->dropForeign(['location_id']);
        });

        DB::statement('ALTER TABLE warehouses CHANGE location_id location_owner_id INT UNSIGNED NOT NULL');
        Schema::table('warehouses', function (Blueprint $t) {
            $t->foreign('location_owner_id')->references('location_id')->on('locations')->cascadeOnDelete();
        });
    }
};
