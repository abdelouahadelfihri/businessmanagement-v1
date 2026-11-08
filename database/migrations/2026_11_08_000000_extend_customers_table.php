<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the minimal customers table (name, email, phone, address) into a full
 * customer master record: identification, contact, addresses, Moroccan legal
 * identifiers (ICE / IF / RC / Patente), commercial terms and an active flag.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Identification
            $table->string('code', 20)->nullable()->unique()->after('id');
            $table->enum('type', ['company', 'individual'])->default('company')->after('code');
            $table->string('contact_person')->nullable()->after('name');

            // Contact
            $table->string('mobile', 30)->nullable()->after('phone');
            $table->string('website')->nullable()->after('mobile');

            // Addresses
            $table->string('city', 100)->nullable()->after('address');
            $table->string('postal_code', 20)->nullable()->after('city');
            $table->string('country', 100)->default('Morocco')->after('postal_code');
            $table->text('shipping_address')->nullable()->after('country');

            // Legal identifiers (Morocco)
            $table->string('ice', 20)->nullable()->after('shipping_address');      // Identifiant Commun de l'Entreprise
            $table->string('tax_id', 30)->nullable()->after('ice');                // Identifiant Fiscal (IF)
            $table->string('trade_register', 30)->nullable()->after('tax_id');     // Registre de Commerce (RC)
            $table->string('patente', 30)->nullable()->after('trade_register');

            // Commercial terms
            $table->unsignedSmallInteger('payment_terms_days')->default(30)->after('patente');
            $table->decimal('credit_limit', 15, 2)->default(0)->after('payment_terms_days'); // 0 = no limit
            $table->decimal('discount_percent', 5, 2)->default(0)->after('credit_limit');
            $table->string('currency', 3)->default('MAD')->after('discount_percent');

            // Misc
            $table->text('notes')->nullable()->after('currency');
            $table->boolean('is_active')->default(true)->after('notes');

            $table->index('name');
            $table->index('is_active');
        });

        // Give any existing rows a code
        DB::table('customers')->whereNull('code')->orderBy('id')->get(['id'])->each(function ($row) {
            DB::table('customers')->where('id', $row->id)->update(['code' => sprintf('CUS-%05d', $row->id)]);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['is_active']);
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code', 'type', 'contact_person', 'mobile', 'website',
                'city', 'postal_code', 'country', 'shipping_address',
                'ice', 'tax_id', 'trade_register', 'patente',
                'payment_terms_days', 'credit_limit', 'discount_percent', 'currency',
                'notes', 'is_active',
            ]);
        });
    }
};
