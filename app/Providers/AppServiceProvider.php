<?php

namespace App\Providers;

use App\Models\CRM\Prospect;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Supplier;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Short, stable type names stored in polymorphic columns (contacts.contactable_type)
        Relation::morphMap([
            'customer' => Customer::class,
            'supplier' => Supplier::class,
            'prospect' => Prospect::class,
        ]);
    }
}
