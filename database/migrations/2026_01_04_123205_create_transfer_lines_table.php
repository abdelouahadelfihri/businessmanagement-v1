<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfer_lines', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id'); // UNSIGNED INT

            $table->unsignedInteger('transfer_id'); // MUST MATCH transfers.id
            $table->unsignedInteger('product_id');  // MUST MATCH products.id

            $table->integer('quantity');

            $table->timestamps();

            $table->foreign('transfer_id')
                ->references('id')
                ->on('transfers')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_lines');
    }
};