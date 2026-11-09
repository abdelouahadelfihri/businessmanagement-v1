<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

/**
 * Creates the default roles and the first administrator account.
 * Safe to run more than once: existing roles and users are left as they are.
 *
 *   php artisan db:seed --class=RolesAndAdminSeeder
 *
 * The admin account uses ADMIN_EMAIL / ADMIN_PASSWORD from .env when set,
 * otherwise the defaults below. Change the password after the first sign-in.
 */
class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $all = Permissions::all();
        $module = fn (array $modules, array $actions = null) => array_values(array_filter(
            $all,
            function ($p) use ($modules, $actions) {
                [$m, $a] = explode('.', $p);
                return in_array($m, $modules, true) && ($actions === null || in_array($a, $actions, true));
            }
        ));

        $sales = ['salesquotations', 'salesorders', 'salesdeliveries', 'salesinvoices', 'salesreturns'];
        $purchases = ['purchasesrequests', 'purchasesorders', 'purchasesreceipts', 'purchasesinvoices'];
        $crm = ['prospects', 'contacts'];
        $stock = ['products', 'categories', 'units', 'warehouses', 'locations', 'stock', 'stocksmovements', 'transfers'];
        $business = array_merge($sales, $purchases, $crm, ['customers', 'suppliers'], $stock);

        $roles = [
            ['Administrator', 'Full access to every module, users and roles.', true, []],
            ['Manager', 'Runs the business: every module except user and role management.', false,
                $module($business)],
            ['Sales', 'Quotations, orders, deliveries, invoices, returns, CRM and customers.', false,
                array_merge($module($sales), $module($crm), $module(['customers']), $module(['products', 'stock'], ['view']))],
            ['Purchasing', 'Purchase requests, orders, receipts, invoices and suppliers.', false,
                array_merge($module($purchases), $module(['suppliers']), $module(['products', 'stock'], ['view']))],
            ['Warehouse', 'Products, stock levels, movements and transfers; sees deliveries and receipts.', false,
                array_merge($module($stock), $module(['salesdeliveries', 'purchasesreceipts'], ['view', 'approve']))],
            ['Viewer', 'Read-only access to every business module.', false,
                $module($business, ['view'])],
        ];

        foreach ($roles as [$name, $description, $super, $permissions]) {
            Role::firstOrCreate(['name' => $name], [
                'description' => $description,
                'is_super' => $super,
                'permissions' => $permissions,
            ]);
        }

        $admin = Role::where('is_super', true)->first();

        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => env('ADMIN_PASSWORD', 'Admin12345'),
                'role_id' => $admin->id,
                'is_active' => true,
            ]
        );
    }
}
