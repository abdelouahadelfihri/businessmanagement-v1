<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Business Management')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #B0C4DE;
            --sidebar-width: 230px;
            --sidebar-collapsed-width: 72px;
            --topbar-height: 56px;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            overflow: hidden;
        }

        /* Layout wrapper */
        .layout {
            display: flex;
            height: 100vh;
            height: 100dvh;
        }

        /* Sidebar: Bootstrap off-canvas drawer below lg, fixed column from lg up */
        .sidebar {
            --bs-offcanvas-width: 270px;
            --bs-offcanvas-bg: var(--sidebar-bg);
            color: #000;
        }

        /* offcanvas-lg forces a transparent background on desktop, so restore it */
        .sidebar.offcanvas-lg {
            background-color: var(--sidebar-bg) !important;
        }

        .sidebar .offcanvas-body {
            overflow-y: auto;
            overflow-x: hidden;
        }

        @media (min-width: 992px) {
            .sidebar {
                width: var(--sidebar-width);
                height: 100vh;
                height: 100dvh;
                flex-shrink: 0;
                transition: width 0.3s;
            }

            .sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }

            .sidebar.collapsed .app-brand span,
            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .submenu {
                display: none;
            }
        }

        /* Sidebar scrollbar */
        .sidebar .offcanvas-body::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar .offcanvas-body::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 6px;
        }

        .sidebar .offcanvas-body::-webkit-scrollbar-track {
            background: transparent;
        }

        /* App header */
        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .app-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            white-space: nowrap;
            color: #000;
            text-decoration: none;
        }

        /* Toggle button */
        .toggle-btn {
            background: rgba(176, 196, 222, 0.45);
            border: none;
            color: #000;
            border-radius: 8px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.25rem;
        }

        .toggle-btn:hover {
            background: rgba(176, 196, 222, 0.8);
        }

        /* Links */
        .sidebar a {
            color: #000;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            white-space: nowrap;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .sidebar .nav-link.active-link {
            background: rgba(255, 255, 255, 0.65);
            font-weight: 600;
        }

        /* Submenus */
        .submenu .nav-link {
            padding-left: 36px;
            font-size: 0.9rem;
        }

        /* Third level indentation */
        .submenu .submenu .nav-link {
            padding-left: 52px;
            font-size: 0.85rem;
        }

        /* Main column (topbar + content) */
        .main {
            flex-grow: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: var(--topbar-height);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            background: #fff;
            border-bottom: 1px solid #e9edf3;
        }

        /* Content */
        .content {
            flex-grow: 1;
            min-width: 0;
            padding: 20px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        @media (max-width: 575.98px) {
            .topbar {
                padding: 0 12px;
            }

            .content {
                padding: 12px;
            }
        }

        /* Extra colours used by badges / buttons (Bootstrap has no purple / orange utilities) */
        .bg-purple { background-color: #7c4dff !important; color: #fff; }
        .bg-orange { background-color: #fd7e14 !important; color: #fff; }
        .text-purple { color: #7c4dff !important; }
        .btn-orange { background-color: #fd7e14; border-color: #fd7e14; color: #fff; }
        .btn-orange:hover, .btn-orange:disabled { background-color: #e8590c; border-color: #e8590c; color: #fff; }

        /* Contact avatars */
        .contact-avatar {
            width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: #e7efff; color: #2f6bff; font-weight: 600; font-size: .9rem;
        }
        .contact-avatar-sm { width: 30px; height: 30px; font-size: .75rem; }
        .contact-avatar-lg { width: 60px; height: 60px; font-size: 1.3rem; }
        .min-w-0 { min-width: 0; }
        @media (min-width: 768px) { .border-end-md { border-right: 1px solid var(--bs-border-color); } }
    </style>
    @stack('styles')
</head>

<body>

    <div class="layout">

        <!-- SIDEBAR -->
        <nav class="sidebar offcanvas-lg offcanvas-start" id="sidebar" tabindex="-1" aria-label="Main navigation">
          <div class="offcanvas-body d-flex flex-column p-3 h-100">

            <!-- App header -->
            <div class="app-header">
                <a href="{{ route('dashboard') }}" class="app-brand">
                    <i class="bi bi-building"></i>
                    <span>Business MS</span>
                </a>
                <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close menu"></button>
            </div>

            <ul class="nav nav-pills flex-column mb-auto">

                <!-- Dashboard -->
                <li>
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active-link' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Purchases -->
                @cananyroute('purchases.purchasesrequests.index', 'purchases.purchasesrequests.create', 'purchases.purchasesorders.index', 'purchases.purchasesorders.create', 'purchases.purchasesreceipts.index', 'purchases.purchasesreceipts.create', 'purchases.purchasesinvoices.index', 'purchases.purchasesinvoices.create')
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#purchasesMenu">
                        <i class="bi bi-cart"></i>
                        <span>Purchases</span>
                    </a>

                    <div class="collapse" id="purchasesMenu">
                        <ul class="nav flex-column submenu">
                            @cananyroute('purchases.purchasesrequests.index', 'purchases.purchasesrequests.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesRequestsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchases Requests</span>
                                </a>
                                <div class="collapse" id="purchasesRequestsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('purchases.purchasesrequests.index')
                                        <li>
                                            @canroute('purchases.purchasesrequests.index')
                                            <a class="nav-link" href="{{ route('purchases.purchasesrequests.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('purchases.purchasesrequests.create')
                                        <li>
                                            @canroute('purchases.purchasesrequests.create')
                                            <a class="nav-link" href="{{ route('purchases.purchasesrequests.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('purchases.purchasesorders.index', 'purchases.purchasesorders.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesOrdersMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchases Orders</span>
                                </a>
                                <div class="collapse" id="purchasesOrdersMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('purchases.purchasesorders.index')
                                        <li>
                                            @canroute('purchases.purchasesorders.index')
                                            <a class="nav-link" href="{{ route('purchases.purchasesorders.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('purchases.purchasesorders.create')
                                        <li>
                                            @canroute('purchases.purchasesorders.create')
                                            <a class="nav-link" href="{{ route('purchases.purchasesorders.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('purchases.purchasesreceipts.index', 'purchases.purchasesreceipts.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesReceiptsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Purchases Receipts</span>
                                </a>
                                <div class="collapse" id="purchasesReceiptsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('purchases.purchasesreceipts.index')
                                        <li>
                                            @canroute('purchases.purchasesreceipts.index')
                                            <a class="nav-link" href="{{ route('purchases.purchasesreceipts.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('purchases.purchasesreceipts.create')
                                        <li>
                                            @canroute('purchases.purchasesreceipts.create')
                                            <a class="nav-link" href="{{ route('purchases.purchasesreceipts.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('purchases.purchasesinvoices.index', 'purchases.purchasesinvoices.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesInvoicesMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchases Invoices</span>
                                </a>
                                <div class="collapse" id="purchasesInvoicesMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('purchases.purchasesinvoices.index')
                                        <li>
                                            @canroute('purchases.purchasesinvoices.index')
                                            <a class="nav-link" href="{{ route('purchases.purchasesinvoices.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('purchases.purchasesinvoices.create')
                                        <li>
                                            @canroute('purchases.purchasesinvoices.create')
                                            <a class="nav-link" href="{{ route('purchases.purchasesinvoices.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                        </ul>
                    </div>
                </li>
                @endcananyroute

                <!-- Sales -->
                @cananyroute('sales.salesquotations.index', 'sales.salesquotations.create', 'sales.salesorders.index', 'sales.salesorders.create', 'sales.salesdeliveries.index', 'sales.salesdeliveries.create', 'sales.salesinvoices.index', 'sales.salesinvoices.create', 'sales.salesreturns.index', 'sales.salesreturns.create')
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#salesMenu">
                        <i class="bi bi-cash-stack"></i>
                        <span>Sales</span>
                    </a>

                    <div class="collapse" id="salesMenu">
                        <ul class="nav flex-column submenu">
                            @cananyroute('sales.salesquotations.index', 'sales.salesquotations.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesQuotationsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Sales Quotations</span>
                                </a>
                                <div class="collapse" id="salesQuotationsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('sales.salesquotations.index')
                                        <li>
                                            @canroute('sales.salesquotations.index')
                                            <a class="nav-link" href="{{ route('sales.salesquotations.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('sales.salesquotations.create')
                                        <li>
                                            @canroute('sales.salesquotations.create')
                                            <a class="nav-link" href="{{ route('sales.salesquotations.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('sales.salesorders.index', 'sales.salesorders.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesOrdersMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Sales Orders</span>
                                </a>
                                <div class="collapse" id="salesOrdersMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('sales.salesorders.index')
                                        <li>
                                            @canroute('sales.salesorders.index')
                                            <a class="nav-link" href="{{ route('sales.salesorders.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('sales.salesorders.create')
                                        <li>
                                            @canroute('sales.salesorders.create')
                                            <a class="nav-link" href="{{ route('sales.salesorders.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('sales.salesdeliveries.index', 'sales.salesdeliveries.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesDeliveriesMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Sales Deliveries</span>
                                </a>
                                <div class="collapse" id="salesDeliveriesMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('sales.salesdeliveries.index')
                                        <li>
                                            @canroute('sales.salesdeliveries.index')
                                            <a class="nav-link" href="{{ route('sales.salesdeliveries.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('sales.salesdeliveries.create')
                                        <li>
                                            @canroute('sales.salesdeliveries.create')
                                            <a class="nav-link" href="{{ route('sales.salesdeliveries.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('sales.salesinvoices.index', 'sales.salesinvoices.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesInvoicesMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Sales Invoices</span>
                                </a>
                                <div class="collapse" id="salesInvoicesMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('sales.salesinvoices.index')
                                        <li>
                                            @canroute('sales.salesinvoices.index')
                                            <a class="nav-link" href="{{ route('sales.salesinvoices.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('sales.salesinvoices.create')
                                        <li>
                                            @canroute('sales.salesinvoices.create')
                                            <a class="nav-link" href="{{ route('sales.salesinvoices.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('sales.salesreturns.index', 'sales.salesreturns.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesReturnsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Sales Returns</span>
                                </a>
                                <div class="collapse" id="salesReturnsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('sales.salesreturns.index')
                                        <li>
                                            @canroute('sales.salesreturns.index')
                                            <a class="nav-link" href="{{ route('sales.salesreturns.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('sales.salesreturns.create')
                                        <li>
                                            @canroute('sales.salesreturns.create')
                                            <a class="nav-link" href="{{ route('sales.salesreturns.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                        </ul>
                    </div>
                </li>
                @endcananyroute

                <!-- CRM -->
                @cananyroute('crm.prospects.index', 'crm.prospects.create', 'crm.contacts.index', 'crm.contacts.create')
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#crmMenu">
                        <i class="bi bi-people"></i>
                        <span>CRM</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('crm.*') ? 'show' : '' }}" id="crmMenu">
                        <ul class="nav flex-column submenu">
                            @cananyroute('crm.prospects.index', 'crm.prospects.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#prospectsMenu">
                                    <i class="bi bi-bullseye"></i>
                                    <span>Prospects</span>
                                </a>
                                <div class="collapse {{ request()->routeIs('crm.prospects.*') ? 'show' : '' }}" id="prospectsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('crm.prospects.index')
                                        <li>
                                            @canroute('crm.prospects.index')
                                            <a class="nav-link {{ request()->routeIs('crm.prospects.index') ? 'active-link' : '' }}" href="{{ route('crm.prospects.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('crm.prospects.create')
                                        <li>
                                            @canroute('crm.prospects.create')
                                            <a class="nav-link {{ request()->routeIs('crm.prospects.create') ? 'active-link' : '' }}" href="{{ route('crm.prospects.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('crm.contacts.index', 'crm.contacts.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#contactsMenu">
                                    <i class="bi bi-person-lines-fill"></i>
                                    <span>Contacts</span>
                                </a>
                                <div class="collapse {{ request()->routeIs('crm.contacts.*') ? 'show' : '' }}" id="contactsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('crm.contacts.index')
                                        <li>
                                            @canroute('crm.contacts.index')
                                            <a class="nav-link {{ request()->routeIs('crm.contacts.index') ? 'active-link' : '' }}" href="{{ route('crm.contacts.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('crm.contacts.create')
                                        <li>
                                            @canroute('crm.contacts.create')
                                            <a class="nav-link {{ request()->routeIs('crm.contacts.create') ? 'active-link' : '' }}" href="{{ route('crm.contacts.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                        </ul>
                    </div>
                </li>
                @endcananyroute

                <!-- Inventories -->
                @cananyroute('masterdata.customers.index', 'masterdata.customers.create', 'masterdata.suppliers.index', 'masterdata.suppliers.create', 'masterdata.products.index', 'masterdata.products.create', 'masterdata.categories.index', 'masterdata.categories.create', 'masterdata.units.index', 'masterdata.units.create', 'masterdata.warehouses.index', 'masterdata.warehouses.create', 'masterdata.locations.index', 'masterdata.locations.create', 'masterdata.stocksmovements.index')
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#masterDataMenu">
                        <i class="bi bi-box-seam"></i>
                        <span>Master Data</span>
                    </a>

                    <div class="collapse" id="masterDataMenu">
                        <ul class="nav flex-column submenu">
                            @cananyroute('masterdata.customers.index', 'masterdata.customers.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#customersMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Customers</span>
                                </a>
                                <div class="collapse" id="customersMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.customers.index')
                                        <li>
                                            @canroute('masterdata.customers.index')
                                            <a class="nav-link" href="{{ route('masterdata.customers.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('masterdata.customers.create')
                                        <li>
                                            @canroute('masterdata.customers.create')
                                            <a class="nav-link" href="{{ route('masterdata.customers.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('masterdata.suppliers.index', 'masterdata.suppliers.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#suppliersMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Suppliers</span>
                                </a>
                                <div class="collapse" id="suppliersMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.suppliers.index')
                                        <li>
                                            @canroute('masterdata.suppliers.index')
                                            <a class="nav-link" href="{{ route('masterdata.suppliers.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('masterdata.suppliers.create')
                                        <li>
                                            @canroute('masterdata.suppliers.create')
                                            <a class="nav-link" href="{{ route('masterdata.suppliers.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('masterdata.products.index', 'masterdata.products.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#productsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Products</span>
                                </a>
                                <div class="collapse" id="productsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.products.index')
                                        <li>
                                            @canroute('masterdata.products.index')
                                            <a class="nav-link" href="{{ route('masterdata.products.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('masterdata.products.create')
                                        <li>
                                            @canroute('masterdata.products.create')
                                            <a class="nav-link" href="{{ route('masterdata.products.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('masterdata.categories.index', 'masterdata.categories.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#categoriesMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Categories</span>
                                </a>
                                <div class="collapse" id="categoriesMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.categories.index')
                                        <li>
                                            @canroute('masterdata.categories.index')
                                            <a class="nav-link" href="{{ route('masterdata.categories.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('masterdata.categories.create')
                                        <li>
                                            @canroute('masterdata.categories.create')
                                            <a class="nav-link" href="{{ route('masterdata.categories.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('masterdata.units.index', 'masterdata.units.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#unitsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Units</span>
                                </a>
                                <div class="collapse" id="unitsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.units.index')
                                        <li>
                                            @canroute('masterdata.units.index')
                                            <a class="nav-link" href="{{ route('masterdata.units.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('masterdata.units.create')
                                        <li>
                                            @canroute('masterdata.units.create')
                                            <a class="nav-link" href="{{ route('masterdata.units.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('masterdata.warehouses.index', 'masterdata.warehouses.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#warehousesMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Warehouses</span>
                                </a>
                                <div class="collapse" id="warehousesMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.warehouses.index')
                                        <li>
                                            @canroute('masterdata.warehouses.index')
                                            <a class="nav-link" href="{{ route('masterdata.warehouses.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('masterdata.warehouses.create')
                                        <li>
                                            @canroute('masterdata.warehouses.create')
                                            <a class="nav-link" href="{{ route('masterdata.warehouses.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('masterdata.locations.index', 'masterdata.locations.create')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#locationsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Locations</span>
                                </a>
                                <div class="collapse" id="locationsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.locations.index')
                                        <li>
                                            @canroute('masterdata.locations.index')
                                            <a class="nav-link" href="{{ route('masterdata.locations.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                        @cananyroute('masterdata.locations.create')
                                        <li>
                                            @canroute('masterdata.locations.create')
                                            <a class="nav-link" href="{{ route('masterdata.locations.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                            @cananyroute('masterdata.stocksmovements.index')
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#stocksMovementsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Stocks Movements</span>
                                </a>
                                <div class="collapse" id="stocksMovementsMenu">
                                    <ul class="nav flex-column submenu">
                                        @cananyroute('masterdata.stocksmovements.index')
                                        <li>
                                            @canroute('masterdata.stocksmovements.index')
                                            <a class="nav-link" href="{{ route('masterdata.stocksmovements.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                            @endcanroute
                                        </li>
                                        @endcananyroute
                                    </ul>
                                </div>
                            </li>
                            @endcananyroute
                        </ul>
                    </div>
                </li>
                @endcananyroute

                <!-- Administration -->
                @cananyroute('admin.dashboard', 'admin.users.index', 'admin.roles.index')
                <li>
                    <a class="nav-link {{ request()->routeIs('admin.*') ? 'active-link' : '' }}" data-bs-toggle="collapse" href="#adminMenu">
                        <i class="bi bi-shield-lock"></i>
                        <span>Administration</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminMenu">
                        <ul class="nav flex-column submenu">
                            @canroute('admin.dashboard')
                            <li>
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active-link' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-grid-1x2"></i>
                                    <span>Admin panel</span>
                                </a>
                            </li>
                            @endcanroute
                            @canroute('admin.users.index')
                            <li>
                                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active-link' : '' }}" href="{{ route('admin.users.index') }}">
                                    <i class="bi bi-people"></i>
                                    <span>Users</span>
                                </a>
                            </li>
                            @endcanroute
                            @canroute('admin.roles.index')
                            <li>
                                <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active-link' : '' }}" href="{{ route('admin.roles.index') }}">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Roles &amp; permissions</span>
                                </a>
                            </li>
                            @endcanroute
                        </ul>
                    </div>
                </li>
                @endcananyroute

            </ul>
          </div>
        </nav>

        <div class="main">
            <!-- TOPBAR -->
            <header class="topbar">
                <!-- Phones / tablets: open the off-canvas menu -->
                <button type="button" class="toggle-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>
                <!-- Desktop: collapse the sidebar to icons -->
                <button type="button" class="toggle-btn d-none d-lg-flex" onclick="toggleSidebar()" aria-label="Collapse menu">
                    <i class="bi bi-list"></i>
                </button>
                <a href="{{ route('dashboard') }}" class="app-brand d-lg-none">
                    <i class="bi bi-building"></i>
                    <span>Business MS</span>
                </a>

                @auth
                    <div class="dropdown ms-auto">
                        <button class="btn btn-light border-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="contact-avatar contact-avatar-sm">{{ auth()->user()->initials() }}</span>
                            <span class="d-none d-sm-flex flex-column text-start lh-sm">
                                <span class="fw-semibold small">{{ auth()->user()->name }}</span>
                                <span class="text-muted" style="font-size: .75rem;">{{ auth()->user()->role?->name ?? 'No role' }}</span>
                            </span>
                            <i class="bi bi-chevron-down small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="px-3 py-2 small text-muted text-truncate" style="max-width: 260px;">{{ auth()->user()->email }}</li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My profile</a></li>
                            @cananyroute('admin.dashboard')
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock me-2"></i>Admin panel</a></li>
                            @endcananyroute
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </header>

            <!-- CONTENT -->
            <main class="content">
                @yield('content')
            </main>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const KEY = 'sidebar-collapsed';

            // Restore the desktop collapsed state
            try {
                if (localStorage.getItem(KEY) === '1') sidebar.classList.add('collapsed');
            } catch (e) {}

            window.toggleSidebar = function () {
                const collapsed = sidebar.classList.toggle('collapsed');
                try { localStorage.setItem(KEY, collapsed ? '1' : '0'); } catch (e) {}
            };

            // Clicking a menu group while collapsed expands the sidebar so its submenu is visible
            sidebar.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (sidebar.classList.contains('collapsed') && window.innerWidth >= 992) toggleSidebar();
                });
            });

            // Growing past the mobile breakpoint: close the drawer cleanly
            window.matchMedia('(min-width: 992px)').addEventListener('change', function (e) {
                if (e.matches) {
                    const oc = bootstrap.Offcanvas.getInstance(sidebar);
                    if (oc) oc.hide();
                }
            });
        })();
    </script>
    @stack('scripts')
</body>

</html>