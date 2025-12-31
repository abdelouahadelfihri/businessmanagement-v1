<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Polymorphic reference to document (purchase, invoice, etc.)
            $table->nullableMorphs('source'); // source_type + source_id

            // Optional origin warehouse for transfers
            $table->unsignedBigInteger('source_warehouse_id')->nullable();
            $table->foreign('source_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropMorphs('source');
            $table->dropForeign(['source_warehouse_id']);
            $table->dropColumn('source_warehouse_id');
        });
    }
};