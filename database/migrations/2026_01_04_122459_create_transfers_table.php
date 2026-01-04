<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('transfer_number')->unique();

            $table->unsignedInteger('from_warehouse_id');
            $table->unsignedInteger('to_warehouse_id');

            $table->date('transfer_date');

            $table->enum('status', ['draft', 'posted', 'cancelled'])
                ->default('posted');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('from_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict');

            $table->foreign('to_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};