<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safe to run whether or not sales_deliveries / sale_delivery_lines already exist.
 * (The quantity change requires Laravel 11+, or doctrine/dbal on Laravel 10.)
 */
return new class extends Migration {
    private array $deliveryColumns = [
        'currency', 'carrier', 'tracking_number', 'shipping_address', 'recipient_name', 'recipient_phone',
        'notes', 'internal_notes', 'subtotal', 'tax',
        'validated_at', 'cancelled_at', 'cancellation_reason', 'created_by',
    ];

    private array $lineColumns = [
        'order_line_id', 'description', 'unit', 'discount_percent', 'tax_rate', 'total',
    ];

    public function up(): void
    {
        /* ---------------- sales_deliveries ---------------- */
        if (!Schema::hasTable('sales_deliveries')) {
            Schema::create('sales_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_order_id');
                $table->string('delivery_number')->unique();
                $table->date('date');
                $table->string('status', 20)->default('draft');
                $table->decimal('total', 15, 2)->default(0);
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('sales_deliveries', function (Blueprint $table) {
            $missing = fn(string $c) => !Schema::hasColumn('sales_deliveries', $c);

            if ($missing('currency'))            $table->string('currency', 3)->default('MAD');
            if ($missing('carrier'))             $table->string('carrier', 100)->nullable();
            if ($missing('tracking_number'))     $table->string('tracking_number', 100)->nullable();
            if ($missing('shipping_address'))    $table->text('shipping_address')->nullable();
            if ($missing('recipient_name'))      $table->string('recipient_name', 150)->nullable();
            if ($missing('recipient_phone'))     $table->string('recipient_phone', 30)->nullable();
            if ($missing('notes'))               $table->text('notes')->nullable();
            if ($missing('internal_notes'))      $table->text('internal_notes')->nullable();
            if ($missing('subtotal'))            $table->decimal('subtotal', 15, 2)->default(0);
            if ($missing('tax'))                 $table->decimal('tax', 15, 2)->default(0);
            if ($missing('validated_at'))        $table->timestamp('validated_at')->nullable();
            if ($missing('cancelled_at'))        $table->timestamp('cancelled_at')->nullable();
            if ($missing('cancellation_reason')) $table->string('cancellation_reason')->nullable();
            if ($missing('created_by'))          $table->unsignedBigInteger('created_by')->nullable();
        });

        /* ---------------- sale_delivery_lines ---------------- */
        if (!Schema::hasTable('sale_delivery_lines')) {
            Schema::create('sale_delivery_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_delivery_id')->index();
                $table->unsignedBigInteger('order_line_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('description')->default('');
                $table->decimal('quantity', 15, 3)->default(0);
                $table->string('unit', 20)->nullable();
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(20);
                $table->decimal('total', 15, 2)->default(0);
                $table->timestamps();
            });
            return;
        }

        Schema::table('sale_delivery_lines', function (Blueprint $table) {
            $missing = fn(string $c) => !Schema::hasColumn('sale_delivery_lines', $c);

            if ($missing('order_line_id'))    $table->unsignedBigInteger('order_line_id')->nullable()->index();
            if ($missing('description'))      $table->string('description')->default('');
            if ($missing('unit'))             $table->string('unit', 20)->nullable();
            if ($missing('discount_percent')) $table->decimal('discount_percent', 5, 2)->default(0);
            if ($missing('tax_rate'))         $table->decimal('tax_rate', 5, 2)->default(20);
            if ($missing('total'))            $table->decimal('total', 15, 2)->default(0);
        });

        // Quantities must accept decimals
        Schema::table('sale_delivery_lines', fn(Blueprint $t) => $t->decimal('quantity', 15, 3)->default(0)->change());
    }

    public function down(): void
    {
        foreach ([['sales_deliveries', $this->deliveryColumns], ['sale_delivery_lines', $this->lineColumns]] as [$tableName, $cols]) {
            if (!Schema::hasTable($tableName)) continue;
            $existing = array_values(array_filter($cols, fn($c) => Schema::hasColumn($tableName, $c)));
            if ($existing) {
                Schema::table($tableName, fn(Blueprint $t) => $t->dropColumn($existing));
            }
        }
    }
};
