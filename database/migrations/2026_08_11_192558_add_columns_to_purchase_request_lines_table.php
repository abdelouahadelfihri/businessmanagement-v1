<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_request_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_request_lines', 'description')) {
                $table->text('description')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('purchase_request_lines', 'unit')) {
                $table->string('unit')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: guarded repair migration.
    }
};