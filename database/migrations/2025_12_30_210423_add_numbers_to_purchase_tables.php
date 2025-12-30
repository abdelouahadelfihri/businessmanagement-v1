<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('pr_number')->nullable()->after('id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('pr_number');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('po_number');
        });
    }
};
