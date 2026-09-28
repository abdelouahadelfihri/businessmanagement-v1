<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'requested_by')) {
                $table->unsignedBigInteger('requested_by')->nullable()->after('supplier_id');
            }
            if (!Schema::hasColumn('purchase_requests', 'expected_date')) {
                $table->date('expected_date')->nullable()->after('date');
            }
            if (!Schema::hasColumn('purchase_requests', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            }
            if (!Schema::hasColumn('purchase_requests', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('purchase_requests', 'currency')) {
                $table->string('currency', 3)->default('MAD');
            }
            if (!Schema::hasColumn('purchase_requests', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }
            if (!Schema::hasColumn('purchase_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('purchase_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('purchase_requests', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('purchase_requests', 'attachment')) {
                $table->string('attachment')->nullable();
            }
        });

        // Foreign keys in a second step, after the columns exist
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Intentionally empty: guarded repair migration.
    }
};