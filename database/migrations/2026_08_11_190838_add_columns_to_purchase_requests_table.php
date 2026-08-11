<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('pr_number')->unique()->after('id');
            $table->unsignedBigInteger('requested_by')->nullable()->after('supplier_id');
            $table->date('expected_date')->nullable()->after('date');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('expected_date');
            $table->decimal('total_amount', 12, 2)->default(0)->after('status');
            $table->string('currency', 3)->default('MAD')->after('total_amount');
            $table->unsignedBigInteger('approved_by')->nullable()->after('currency');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->text('notes')->nullable()->after('rejection_reason');
            $table->string('attachment')->nullable()->after('notes');

            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'pr_number',
                'requested_by',
                'expected_date',
                'priority',
                'total_amount',
                'currency',
                'approved_by',
                'approved_at',
                'rejection_reason',
                'notes',
                'attachment',
            ]);
        });
    }
};