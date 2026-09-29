<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_lines', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_order_lines', 'description')) {
                $table->string('description')->nullable();
            }
            if (!Schema::hasColumn('purchase_order_lines', 'received_quantity')) {
                $table->decimal('received_quantity', 12, 3)->default(0);
            }
            if (!Schema::hasColumn('purchase_order_lines', 'unit')) {
                $table->string('unit')->nullable();
            }
            if (!Schema::hasColumn('purchase_order_lines', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('purchase_order_lines', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(20);
            }
            if (!Schema::hasColumn('purchase_order_lines', 'line_total')) {
                $table->decimal('line_total', 15, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: we don't know which of these columns
        // existed before this migration, so we don't drop any of them.
    }
};