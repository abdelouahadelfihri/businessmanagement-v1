<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safe to run whether or not sales_orders / sales_order_lines already exist:
 * missing tables are created, existing tables only receive the missing columns.
 * (Column rename/change requires Laravel 11+, or doctrine/dbal on Laravel 10.)
 */
return new class extends Migration {
    private array $orderColumns = [
        'reference', 'currency', 'expected_delivery_date', 'payment_terms', 'delivery_terms',
        'shipping_address', 'billing_address', 'notes', 'terms_conditions', 'internal_notes',
        'subtotal', 'discount_amount', 'tax', 'shipping_cost',
        'confirmed_at', 'delivered_at', 'cancelled_at', 'cancellation_reason', 'created_by',
    ];

    private array $lineColumns = [
        'description', 'unit', 'delivered_quantity', 'invoiced_quantity', 'tax_rate',
    ];

    public function up(): void
    {
        /* ---------------- sales_orders ---------------- */
        if (!Schema::hasTable('sales_orders')) {
            Schema::create('sales_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('quotation_id')->nullable();
                $table->string('order_number')->unique();
                $table->date('date');
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status', 20)->default('draft');
                $table->timestamps();
            });
        }

        Schema::table('sales_orders', function (Blueprint $table) {
            $missing = fn(string $c) => !Schema::hasColumn('sales_orders', $c);

            if ($missing('reference'))              $table->string('reference', 100)->nullable();
            if ($missing('currency'))               $table->string('currency', 3)->default('MAD');
            if ($missing('expected_delivery_date')) $table->date('expected_delivery_date')->nullable();
            if ($missing('payment_terms'))          $table->string('payment_terms')->nullable();
            if ($missing('delivery_terms'))         $table->string('delivery_terms')->nullable();
            if ($missing('shipping_address'))       $table->text('shipping_address')->nullable();
            if ($missing('billing_address'))        $table->text('billing_address')->nullable();
            if ($missing('notes'))                  $table->text('notes')->nullable();
            if ($missing('terms_conditions'))       $table->text('terms_conditions')->nullable();
            if ($missing('internal_notes'))         $table->text('internal_notes')->nullable();
            if ($missing('subtotal'))               $table->decimal('subtotal', 15, 2)->default(0);
            if ($missing('discount_amount'))        $table->decimal('discount_amount', 15, 2)->default(0);
            if ($missing('tax'))                    $table->decimal('tax', 15, 2)->default(0);
            if ($missing('shipping_cost'))          $table->decimal('shipping_cost', 15, 2)->default(0);
            if ($missing('confirmed_at'))           $table->timestamp('confirmed_at')->nullable();
            if ($missing('delivered_at'))           $table->timestamp('delivered_at')->nullable();
            if ($missing('cancelled_at'))           $table->timestamp('cancelled_at')->nullable();
            if ($missing('cancellation_reason'))    $table->string('cancellation_reason')->nullable();
            if ($missing('created_by'))             $table->unsignedBigInteger('created_by')->nullable();
        });

        /* ---------------- sales_order_lines ---------------- */
        if (!Schema::hasTable('sales_order_lines')) {
            Schema::create('sales_order_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('sales_orders')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('description');
                $table->decimal('quantity', 15, 3)->default(1);
                $table->decimal('delivered_quantity', 15, 3)->default(0);
                $table->decimal('invoiced_quantity', 15, 3)->default(0);
                $table->string('unit', 20)->nullable();
                $table->decimal('price', 15, 2)->default(0);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(20);
                $table->decimal('total', 15, 2)->default(0);
                $table->timestamps();
            });
            return;
        }

        // Old column "discount" becomes "discount_percent"
        if (Schema::hasColumn('sales_order_lines', 'discount') && !Schema::hasColumn('sales_order_lines', 'discount_percent')) {
            Schema::table('sales_order_lines', fn(Blueprint $t) => $t->renameColumn('discount', 'discount_percent'));
        }

        Schema::table('sales_order_lines', function (Blueprint $table) {
            $missing = fn(string $c) => !Schema::hasColumn('sales_order_lines', $c);

            if ($missing('description'))        $table->string('description')->default('');
            if ($missing('unit'))               $table->string('unit', 20)->nullable();
            if ($missing('delivered_quantity')) $table->decimal('delivered_quantity', 15, 3)->default(0);
            if ($missing('invoiced_quantity'))  $table->decimal('invoiced_quantity', 15, 3)->default(0);
            if ($missing('discount_percent'))   $table->decimal('discount_percent', 5, 2)->default(0);
            if ($missing('tax_rate'))           $table->decimal('tax_rate', 5, 2)->default(20);
        });

        // Quantities must accept decimals (0.001 step, like quotations)
        Schema::table('sales_order_lines', fn(Blueprint $t) => $t->decimal('quantity', 15, 3)->default(1)->change());
    }

    public function down(): void
    {
        foreach ([['sales_orders', $this->orderColumns], ['sales_order_lines', $this->lineColumns]] as [$tableName, $cols]) {
            if (!Schema::hasTable($tableName)) continue;
            $existing = array_values(array_filter($cols, fn($c) => Schema::hasColumn($tableName, $c)));
            if ($existing) {
                Schema::table($tableName, fn(Blueprint $t) => $t->dropColumn($existing));
            }
        }
    }
};
