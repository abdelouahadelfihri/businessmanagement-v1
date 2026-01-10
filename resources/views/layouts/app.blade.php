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
        }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            width: 230px;
            background: #B0C4DE;
            color: #000;
            transition: width 0.3s;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: 72px;
        }

        /* Sidebar scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
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
        }

        .sidebar.collapsed .app-brand span {
            display: none;
        }

        /* Toggle button */
        .toggle-btn {
            background: rgba(255, 255, 255, 0.4);
            border: none;
            color: #000;
            border-radius: 8px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.6);
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

        /* Hide text when collapsed */
        .sidebar.collapsed .nav-link span {
            display: none;
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

        .sidebar.collapsed .submenu {
            display: none;
        }

        /* Content */
        .content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .topbar {
            position: fixed;
            top: 10px;
            left: 230px;
            /* same as sidebar width */
            z-index: 1050;
            transition: left 0.3s;
        }

        /* Move toggle when sidebar collapses */
        .sidebar.collapsed+.topbar {
            left: 72px;
        }
    </style>
    @stack('styles')
</head>

<body>

    <div class="layout">

        <!-- SIDEBAR -->
        <nav class="sidebar d-flex flex-column p-3" id="sidebar">

            <!-- App header -->
            <div class="app-header">
                <div class="app-brand">
                    <i class="bi bi-building"></i>
                    <span>Business MS</span>
                </div>
            </div>

            <ul class="nav nav-pills flex-column mb-auto">

                <!-- Purchases -->
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#purchasesMenu">
                        <i class="bi bi-cart"></i>
                        <span>Purchases</span>
                    </a>

                    <div class="collapse" id="purchasesMenu">
                        <ul class="nav flex-column submenu">
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesRequestsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchases Requests</span>
                                </a>
                                <div class="collapse" id="purchasesRequestsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesrequests.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesrequests.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesOrdersMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchases Orders</span>
                                </a>
                                <div class="collapse" id="purchasesOrdersMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesorders.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesorders.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesReceiptsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Purchases Receipts</span>
                                </a>
                                <div class="collapse" id="purchasesReceiptsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesreceipts.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesreceipts.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchasesInvoicesMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchases Invoices</span>
                                </a>
                                <div class="collapse" id="purchasesInvoicesMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesinvoices.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('purchasesinvoices.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Sales -->
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#salesMenu">
                        <i class="bi bi-cash-stack"></i>
                        <span>Sales</span>
                    </a>

                    <div class="collapse" id="salesMenu">
                        <ul class="nav flex-column submenu">
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesQuotationsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Sales Quotations</span>
                                </a>
                                <div class="collapse" id="salesQuotationsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('salesquotations.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('salesquotations.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesOrdersMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Sales Orders</span>
                                </a>
                                <div class="collapse" id="salesOrdersMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('salesorders.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('salesorders.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesDeliveriesMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Sales Deliveries</span>
                                </a>
                                <div class="collapse" id="salesDeliveriesMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('salesdeliveries.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('salesdeliveries.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesInvoicesMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Sales Invoices</span>
                                </a>
                                <div class="collapse" id="salesInvoicesMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('salesinvoices.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('salesinvoices.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#salesReturnsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Sales Returns</span>
                                </a>
                                <div class="collapse" id="salesReturnsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('salesreturns.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('salesreturns.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Inventories -->
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#masterDataMenu">
                        <i class="bi bi-box-seam"></i>
                        <span>Master Data</span>
                    </a>

                    <div class="collapse" id="masterDataMenu">
                        <ul class="nav flex-column submenu">
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#customersMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Customers</span>
                                </a>
                                <div class="collapse" id="customersMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('customers.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('customers.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#suppliersMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Suppliers</span>
                                </a>
                                <div class="collapse" id="suppliersMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('suppliers.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('suppliers.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#productsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Products</span>
                                </a>
                                <div class="collapse" id="productsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('products.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('products.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#categoriesMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Categories</span>
                                </a>
                                <div class="collapse" id="categoriesMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('categories.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('categories.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#unitsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Units</span>
                                </a>
                                <div class="collapse" id="unitsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('units.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('units.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#inventoriesMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Inventories</span>
                                </a>
                                <div class="collapse" id="inventoriesMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('inventories.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('inventories.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#warehousesMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Warehouses</span>
                                </a>
                                <div class="collapse" id="warehousesMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('warehouses.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('warehouses.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#locationsMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Locations</span>
                                </a>
                                <div class="collapse" id="locationsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('locations.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="{{ route('locations.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="collapse" href="#stocksMovementsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Stocks Movements</span>
                                </a>
                                <div class="collapse" id="stocksMovementsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link" href="{{ route('stocksmovements.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </nav>

        <div class="topbar">
            <button class="toggle-btn" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <!-- CONTENT -->
        <div class="content">
            @yield('content')
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
    </script>
    @stack('scripts')
</body>

</html>