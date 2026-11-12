<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns locations into physical sites (where warehouses are):
 * code (auto LOC-001), type, address made optional, city / postal code / country,
 * GPS coordinates, site contact (name, phone, email), notes and an active flag.
 * Name becomes unique. Every step is guarded so the migration can be re-run
 * after a partial failure (MySQL DDL is not transactional).
 */
return new class extends Migration {
    public function up(): void
    {
        // doctrine/dbal is not installed, so column changes use raw SQL
        DB::statement('ALTER TABLE locations MODIFY address VARCHAR(255) NULL');

        $has = fn(string $column) => Schema::hasColumn('locations', $column);

        Schema::table('locations', function (Blueprint $table) use ($has) {
            if (! $has('code')) {
                $table->string('code', 20)->nullable()->unique()->after('location_id');
            }
            if (! $has('type')) {
                $table->enum('type', ['head_office', 'warehouse_site', 'factory', 'shop', 'office', 'other'])
                    ->default('warehouse_site')->after('name');
            }
            if (! $has('city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (! $has('postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('city');
            }
            if (! $has('country')) {
                $table->string('country', 100)->default('Morocco')->after('postal_code');
            }
            if (! $has('latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('country');
            }
            if (! $has('longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! $has('contact_name')) {
                $table->string('contact_name', 100)->nullable()->after('longitude');
            }
            if (! $has('phone')) {
                $table->string('phone', 30)->nullable()->after('contact_name');
            }
            if (! $has('email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (! $has('notes')) {
                $table->text('notes')->nullable()->after('email');
            }
            if (! $has('is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
        });

        $indexes = collect(DB::select('SHOW INDEX FROM locations'))->pluck('Key_name')->all();
        if (! in_array('locations_name_unique', $indexes, true)) {
            Schema::table('locations', fn(Blueprint $t) => $t->unique('name'));
        }

        DB::table('locations')->whereNull('code')->orderBy('location_id')->get(['location_id'])->each(function ($row) {
            DB::table('locations')->where('location_id', $row->location_id)->update(['code' => sprintf('LOC-%03d', $row->location_id)]);
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code', 'type', 'city', 'postal_code', 'country', 'latitude', 'longitude',
                'contact_name', 'phone', 'email', 'notes', 'is_active',
            ]);
        });

        DB::table('locations')->whereNull('address')->update(['address' => '']);
        DB::statement('ALTER TABLE locations MODIFY address VARCHAR(255) NOT NULL');
    }
};
