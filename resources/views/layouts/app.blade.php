<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Business Management')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
        }

        .sidebar {
            min-width: 220px;
            max-width: 220px;
            background-color: #343a40;
            color: #fff;
            transition: all 0.3s;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #495057;
            color: #fff;
        }

        .sidebar .submenu a {
            padding-left: 30px;
        }

        .content {
            padding: 20px;
            flex-grow: 1;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar d-flex flex-column p-3">
            <h4 class="text-center mb-4">BMS</h4>
            <ul class="nav nav-pills flex-column mb-auto">
                <!-- Purchases Menu -->
                <li>
                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#purchasesMenu" role="button"
                        aria-expanded="false" aria-controls="purchasesMenu">
                        Purchases
                    </a>
                    <div class="collapse" id="purchasesMenu">
                        <ul class="nav flex-column submenu">
                            <li><a class="nav-link text-white" data-bs-toggle="collapse"
                                    href="#purchaseRequestsMenu">Purchase Requests</a>
                                <div class="collapse" id="purchaseRequestsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li><a class="nav-link text-white"
                                                href="{{ route('purchasesrequests.index') }}">List</a></li>
                                        <li><a class="nav-link text-white"
                                                href="{{ route('purchasesrequests.create') }}">Add</a></li>
                                    </ul>
                                </div>
                            </li>
                            <li><a class="nav-link text-white" data-bs-toggle="collapse"
                                    href="#purchaseOrdersMenu">Purchase Orders</a>
                                <div class="collapse" id="purchaseOrdersMenu">
                                    <ul class="nav flex-column submenu">
                                        <li><a class="nav-link text-white"
                                                href="{{ route('purchasesorders.index') }}">List</a></li>
                                        <li><a class="nav-link text-white"
                                                href="{{ route('purchasesorders.create') }}">Add</a></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Sales Menu -->
                <li>
                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#salesMenu">Sales</a>
                    <div class="collapse" id="salesMenu">
                        <ul class="nav flex-column submenu">
                            <li><a class="nav-link text-white" data-bs-toggle="collapse"
                                    href="#salesOrdersMenu">Orders</a>
                                <div class="collapse" id="salesOrdersMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link text-white"
                                                href="{{ route('salesorders.index') }}">List</a>
                                        </li>
                                        <li>
                                            <a class="nav-link text-white"
                                                href="{{ route('salesorders.create') }}">Add</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Inventories Menu -->
                <li>
                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#inventoriesMenu">Inventories</a>
                    <div class="collapse" id="inventoriesMenu">
                        <ul class="nav flex-column submenu">
                            <li><a class="nav-link text-white" data-bs-toggle="collapse"
                                    href="#productsMenu">Products</a>
                                <div class="collapse" id="productsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li><a class="nav-link text-white" href="{{ route('products.index') }}">List</a>
                                        </li>
                                        <li><a class="nav-link text-white" href="{{ route('products.create') }}">Add</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main content -->
        <div class="content">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>