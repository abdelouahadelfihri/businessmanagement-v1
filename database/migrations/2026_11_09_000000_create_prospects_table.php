<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prospects (CRM leads): potential customers followed through a sales pipeline
 * and converted into a customer once won.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->unique();
            $table->enum('type', ['company', 'individual'])->default('company');
            $table->string('name');
            $table->string('industry', 100)->nullable();

            // Contact & address
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Morocco');

            // Pipeline
            $table->enum('source', ['website', 'referral', 'cold_call', 'email', 'social_media', 'trade_show', 'advertising', 'other'])
                ->default('other');
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])
                ->default('new');
            $table->decimal('estimated_value', 15, 2)->default(0);
            $table->string('currency', 3)->default('MAD');
            $table->unsignedTinyInteger('probability')->default(10); // %
            $table->date('expected_close_date')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->string('assigned_to', 100)->nullable();
            $table->string('lost_reason')->nullable();
            $table->text('notes')->nullable();

            // Conversion
            $table->foreignId('converted_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('status');
            $table->index('next_follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
