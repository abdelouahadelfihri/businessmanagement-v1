<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customers and suppliers now have a list of contacts instead of a single
 * contact_person text column. Existing values become the primary contact
 * (with the company email / phone), then the column is dropped.
 */
return new class extends Migration {
    private const OWNERS = ['customers' => 'customer', 'suppliers' => 'supplier'];

    public function up(): void
    {
        foreach (self::OWNERS as $table => $morph) {
            if (! Schema::hasColumn($table, 'contact_person')) {
                continue;
            }

            DB::table($table)->whereNotNull('contact_person')->where('contact_person', '!=', '')
                ->orderBy('id')->get(['id', 'contact_person', 'email', 'phone', 'mobile'])
                ->each(function ($row) use ($morph) {
                    $parts = preg_split('/\s+/', trim($row->contact_person), 2);

                    DB::table('contacts')->insert([
                        'contactable_type' => $morph,
                        'contactable_id' => $row->id,
                        'first_name' => $parts[0],
                        'last_name' => $parts[1] ?? null,
                        'email' => $row->email,
                        'phone' => $row->phone,
                        'mobile' => $row->mobile,
                        'is_primary' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

            Schema::table($table, fn(Blueprint $t) => $t->dropColumn('contact_person'));
        }
    }

    public function down(): void
    {
        foreach (self::OWNERS as $table => $morph) {
            Schema::table($table, fn(Blueprint $t) => $t->string('contact_person')->nullable()->after('name'));

            DB::table('contacts')->where('contactable_type', $morph)->where('is_primary', true)->get()
                ->each(fn($c) => DB::table($table)->where('id', $c->contactable_id)
                    ->update(['contact_person' => trim($c->first_name . ' ' . $c->last_name)]));
        }
    }
};
