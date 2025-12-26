<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            // Add quotation_id as nullable (recommended)
            $table->foreignId('quotation_id')
                ->nullable()
                ->after('customer_id') // place it after customer_id column
                ->constrained('sales_quotes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            // Drop the foreign key + column on rollback
            $table->dropForeign(['quotation_id']);
            $table->dropColumn('quotation_id');
        });
    }
};