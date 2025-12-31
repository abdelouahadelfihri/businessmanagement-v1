<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->nullableMorphs('source');

            // Match warehouses.id type
            $table->unsignedInteger('source_warehouse_id')->nullable();
            $table->foreign('source_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['source_warehouse_id']);
            $table->dropColumn('source_warehouse_id');
            $table->dropMorphs('source');
        });
    }
};