<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add each column only if it does not already exist (keeps existing data). */
    private function addMissing(string $table, array $columns): void
    {
        foreach ($columns as $name => $define) {
            if (!Schema::hasColumn($table, $name)) {
                Schema::table($table, fn (Blueprint $t) => $define($t));
            }
        }
    }

    public function up(): void
    {
        // ---------------------------------------------------------- sales_quotes
        if (!Schema::hasTable('sales_quotes')) {
            Schema::create('sales_quotes', function (Blueprint $table) {
                $table->id();
                $table->string('quote_number', 50)->unique();
                $table->unsignedBigInteger('customer_id');
                $table->date('date');
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status', 20)->default('draft')->index();
                $table->timestamps();
            });
        }

        $this->addMissing('sales_quotes', [
            'reference'        => fn ($t) => $t->string('reference', 100)->nullable(),
            'valid_until'      => fn ($t) => $t->date('valid_until')->nullable(),
            'currency'         => fn ($t) => $t->string('currency', 3)->default('MAD'),
            'payment_terms'    => fn ($t) => $t->string('payment_terms', 150)->nullable(),
            'delivery_terms'   => fn ($t) => $t->string('delivery_terms', 150)->nullable(),
            'subtotal'         => fn ($t) => $t->decimal('subtotal', 15, 2)->default(0),
            'discount_amount'  => fn ($t) => $t->decimal('discount_amount', 15, 2)->default(0),
            'tax'              => fn ($t) => $t->decimal('tax', 15, 2)->default(0),
            'shipping_cost'    => fn ($t) => $t->decimal('shipping_cost', 15, 2)->default(0),
            'notes'            => fn ($t) => $t->text('notes')->nullable(),
            'terms_conditions' => fn ($t) => $t->text('terms_conditions')->nullable(),
            'internal_notes'   => fn ($t) => $t->text('internal_notes')->nullable(),
            'created_by'       => fn ($t) => $t->unsignedBigInteger('created_by')->nullable(),
            'sent_at'          => fn ($t) => $t->timestamp('sent_at')->nullable(),
            'accepted_at'      => fn ($t) => $t->timestamp('accepted_at')->nullable(),
            'rejected_at'      => fn ($t) => $t->timestamp('rejected_at')->nullable(),
            'rejection_reason' => fn ($t) => $t->string('rejection_reason', 255)->nullable(),
        ]);

        // ------------------------------------------------------ sales_quote_lines
        if (!Schema::hasTable('sales_quote_lines')) {
            Schema::create('sales_quote_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quote_id')->index();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->decimal('quantity', 15, 3)->default(1);
                $table->decimal('price', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        $this->addMissing('sales_quote_lines', [
            'description'      => fn ($t) => $t->string('description', 255)->nullable(),
            'unit'             => fn ($t) => $t->string('unit', 30)->nullable(),
            'discount_percent' => fn ($t) => $t->decimal('discount_percent', 5, 2)->default(0),
            'tax_rate'         => fn ($t) => $t->decimal('tax_rate', 5, 2)->default(0),
            'sort_order'       => fn ($t) => $t->unsignedInteger('sort_order')->default(0),
        ]);
    }

    public function down(): void
    {
        // Intentionally non-destructive: these tables may predate this migration.
        $quoteCols = ['reference', 'valid_until', 'currency', 'payment_terms', 'delivery_terms', 'subtotal',
            'discount_amount', 'tax', 'shipping_cost', 'notes', 'terms_conditions', 'internal_notes',
            'created_by', 'sent_at', 'accepted_at', 'rejected_at', 'rejection_reason'];
        $lineCols = ['description', 'unit', 'discount_percent', 'tax_rate', 'sort_order'];

        Schema::table('sales_quotes', fn (Blueprint $t) => $t->dropColumn(
            array_values(array_filter($quoteCols, fn ($c) => Schema::hasColumn('sales_quotes', $c)))
        ));
        Schema::table('sales_quote_lines', fn (Blueprint $t) => $t->dropColumn(
            array_values(array_filter($lineCols, fn ($c) => Schema::hasColumn('sales_quote_lines', $c)))
        ));
    }
};