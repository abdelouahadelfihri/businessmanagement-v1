<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the minimal suppliers table (name, email, phone, address) into a full
 * supplier master record: identification, contact, address, Moroccan legal
 * identifiers, bank details, purchasing terms, rating and an active flag.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Identification
            $table->string('code', 20)->nullable()->unique()->after('id');
            $table->enum('type', ['company', 'individual'])->default('company')->after('code');
            $table->string('contact_person')->nullable()->after('name');

            // Contact
            $table->string('mobile', 30)->nullable()->after('phone');
            $table->string('website')->nullable()->after('mobile');

            // Address
            $table->string('city', 100)->nullable()->after('address');
            $table->string('postal_code', 20)->nullable()->after('city');
            $table->string('country', 100)->default('Morocco')->after('postal_code');

            // Legal identifiers (Morocco)
            $table->string('ice', 20)->nullable()->after('country');
            $table->string('tax_id', 30)->nullable()->after('ice');            // IF
            $table->string('trade_register', 30)->nullable()->after('tax_id'); // RC
            $table->string('patente', 30)->nullable()->after('trade_register');

            // Bank details
            $table->string('bank_name', 100)->nullable()->after('patente');
            $table->string('rib', 30)->nullable()->after('bank_name');         // 24-digit Moroccan RIB
            $table->string('swift', 11)->nullable()->after('rib');

            // Purchasing terms
            $table->unsignedSmallInteger('payment_terms_days')->default(30)->after('swift');
            $table->string('payment_method', 20)->default('bank_transfer')->after('payment_terms_days');
            $table->string('currency', 3)->default('MAD')->after('payment_method');
            $table->unsignedSmallInteger('lead_time_days')->nullable()->after('currency');
            $table->decimal('minimum_order', 15, 2)->default(0)->after('lead_time_days');
            $table->decimal('discount_percent', 5, 2)->default(0)->after('minimum_order');

            // Evaluation & misc
            $table->unsignedTinyInteger('rating')->nullable()->after('discount_percent'); // 1..5
            $table->text('notes')->nullable()->after('rating');
            $table->boolean('is_active')->default(true)->after('notes');

            $table->index('name');
            $table->index('is_active');
        });

        // Give existing suppliers a code
        DB::table('suppliers')->whereNull('code')->orderBy('id')->get(['id'])->each(function ($row) {
            DB::table('suppliers')->where('id', $row->id)->update(['code' => sprintf('SUP-%05d', $row->id)]);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['is_active']);
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code', 'type', 'contact_person', 'mobile', 'website',
                'city', 'postal_code', 'country',
                'ice', 'tax_id', 'trade_register', 'patente',
                'bank_name', 'rib', 'swift',
                'payment_terms_days', 'payment_method', 'currency', 'lead_time_days', 'minimum_order', 'discount_percent',
                'rating', 'notes', 'is_active',
            ]);
        });
    }
};
