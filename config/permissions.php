<?php

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
|
| Every protected route is mapped to a permission key "<module>.<action>".
| The module comes from the route name (e.g. "sales.salesorders.confirm"
| -> "salesorders") and the action from the route's last segment through
| the "route_actions" map below. A route that cannot be mapped is only
| reachable by super administrators (deny by default).
|
*/

return [

    // Action labels shown in the role editor, in column order.
    'actions' => [
        'view'    => 'View',
        'create'  => 'Create',
        'edit'    => 'Edit',
        'delete'  => 'Delete',
        'approve' => 'Validate / approve',
        'cancel'  => 'Cancel',
        'payment' => 'Payments',
    ],

    // Route method / last route-name segment => permission action.
    'route_actions' => [
        'index'          => 'view',
        'show'           => 'view',
        'create'         => 'create',
        'store'          => 'create',
        'ajaxStore'      => 'create',
        'from_quotation' => 'create',
        'transfer_form'  => 'create',
        'transfer'       => 'create',
        'defaults'       => 'create',
        'default'        => 'edit',
        'edit'           => 'edit',
        'update'         => 'edit',
        'status'         => 'edit',
        'primary'        => 'edit',
        'destroy'        => 'delete',
        'approve'        => 'approve',
        'reject'         => 'approve',
        'submit'         => 'approve',
        'send'           => 'approve',
        'validate'       => 'approve',
        'accept'         => 'approve',
        'confirm'        => 'approve',
        'process'        => 'approve',
        'deliver'        => 'approve',
        'convert'        => 'approve',
        'complete'       => 'approve',
        'reset_link'     => 'edit',
        'toggle'         => 'edit',
        'cancel'         => 'cancel',
        'payment'        => 'payment',
    ],

    // Route-name module segment => permission module, when they differ.
    'module_aliases' => [
        'stock-movements' => 'stocksmovements',
        'stocksmovements.transfer_form' => 'transfers',
        'stocksmovements.transfer' => 'transfers',
    ],

    // Routes that need any one of the listed permissions.
    'route_permissions' => [
        'admin.dashboard' => ['users.view', 'roles.view'],
    ],

    // Routes any signed-in user may open.
    'public_routes' => [
        'dashboard',
        'logout',
        'profile.edit',
        'profile.update',
        'profile.password',
    ],

    // Modules grouped as in the sidebar, with the actions that apply.
    'modules' => [
        'Sales' => [
            'salesquotations' => ['label' => 'Quotations', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel']],
            'salesorders'     => ['label' => 'Orders', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel']],
            'salesdeliveries' => ['label' => 'Deliveries', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel']],
            'salesinvoices'   => ['label' => 'Invoices', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel', 'payment']],
            'salesreturns'    => ['label' => 'Returns', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel']],
        ],
        'Purchases' => [
            'purchasesrequests' => ['label' => 'Requests', 'actions' => ['view', 'create', 'edit', 'delete', 'approve']],
            'purchasesorders'   => ['label' => 'Orders', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel']],
            'purchasesreceipts' => ['label' => 'Receipts', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel']],
            'purchasesinvoices' => ['label' => 'Invoices', 'actions' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel', 'payment']],
        ],
        'CRM' => [
            'prospects' => ['label' => 'Prospects', 'actions' => ['view', 'create', 'edit', 'delete', 'approve']],
            'contacts'  => ['label' => 'Contacts', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Master data' => [
            'customers'       => ['label' => 'Customers', 'actions' => ['view', 'create', 'edit', 'delete']],
            'suppliers'       => ['label' => 'Suppliers', 'actions' => ['view', 'create', 'edit', 'delete']],
            'products'        => ['label' => 'Products', 'actions' => ['view', 'create', 'edit', 'delete']],
            'categories'      => ['label' => 'Categories', 'actions' => ['view', 'create', 'edit', 'delete']],
            'units'           => ['label' => 'Units', 'actions' => ['view', 'create', 'edit', 'delete']],
            'warehouses'      => ['label' => 'Warehouses', 'actions' => ['view', 'create', 'edit', 'delete']],
            'locations'       => ['label' => 'Locations', 'actions' => ['view', 'create', 'edit', 'delete']],
            'stock'           => ['label' => 'Stock levels', 'actions' => ['view']],
            'stocksmovements' => ['label' => 'Stock movements', 'actions' => ['view', 'create', 'edit', 'delete']],
            'transfers'       => ['label' => 'Transfers', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Administration' => [
            'users' => ['label' => 'Users', 'actions' => ['view', 'create', 'edit', 'delete']],
            'roles' => ['label' => 'Roles & permissions', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
    ],
];
