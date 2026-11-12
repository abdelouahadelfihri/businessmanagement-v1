<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns units of measure into a small conversion system:
 *  - type (count, weight, volume, length, area, time)
 *  - base unit + conversion factor (1 box = 12 pieces, 1 kg = 1000 g)
 *  - decimal places used when entering / showing quantities
 *  - description and active flag
 * Name and abbreviation become unique.
 */
return new class extends Migration {
    public function up(): void
    {
        // doctrine/dbal is not installed, so column changes use raw SQL
        DB::statement('ALTER TABLE units MODIFY abbreviation VARCHAR(20) NULL');

        Schema::table('units', function (Blueprint $table) {
            $table->enum('type', ['count', 'weight', 'volume', 'length', 'area', 'time'])
                ->default('count')->after('abbreviation');
            $table->unsignedBigInteger('base_unit_id')->nullable()->after('type');
            $table->decimal('conversion_factor', 18, 6)->default(1)->after('base_unit_id'); // 1 this = factor x base
            $table->unsignedTinyInteger('decimal_places')->default(0)->after('conversion_factor');
            $table->string('description')->nullable()->after('decimal_places');
            $table->boolean('is_active')->default(true)->after('description');

            $table->foreign('base_unit_id')->references('unit_id')->on('units')->nullOnDelete();
            $table->unique('name');
            $table->unique('abbreviation');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['base_unit_id']);
            $table->dropUnique(['name']);
            $table->dropUnique(['abbreviation']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'base_unit_id', 'conversion_factor', 'decimal_places', 'description', 'is_active']);
        });
    }
};
