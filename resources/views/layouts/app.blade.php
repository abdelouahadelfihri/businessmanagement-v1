<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Business Management')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            height: 100vh;
            width: 220px;
            background-color: #343a40;
            color: #fff;
            transition: width 0.3s;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            white-space: nowrap;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar .submenu .nav-link {
            padding-left: 30px;
            font-size: 0.9rem;
        }

        .sidebar.collapsed .submenu {
            display: none;
        }

        .content {
            padding: 20px;
            flex-grow: 1;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar d-flex flex-column p-3" id="sidebar">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-center m-0">BMS</h4>
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <ul class="nav nav-pills flex-column mb-auto">

                <!-- Purchases -->
                <li>
                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#purchasesMenu">
                        <i class="bi bi-cart"></i>
                        <span>Purchases</span>
                    </a>

                    <div class="collapse" id="purchasesMenu">
                        <ul class="nav flex-column submenu">

                            <!-- Purchase Requests -->
                            <li>
                                <a class="nav-link text-white" data-bs-toggle="collapse" href="#purchaseRequestsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchase Requests</span>
                                </a>

                                <div class="collapse" id="purchaseRequestsMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link text-white"
                                                href="{{ route('purchasesrequests.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link text-white"
                                                href="{{ route('purchasesrequests.create') }}">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Purchase Orders -->
                            <li>
                                <a class="nav-link text-white" data-bs-toggle="collapse" href="#purchaseOrdersMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Purchase Orders</span>
                                </a>

                                <div class="collapse" id="purchaseOrdersMenu">
                                    <ul class="nav flex-column submenu">
                                        <li>
                                            <a class="nav-link text-white" href="{{ route('purchasesorders.index') }}">
                                                <i class="bi bi-list-ul"></i>
                                                <span>List</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link text-white" href="{{ route('purchasesorders.create') }}">
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
                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#salesMenu">
                        <i class="bi bi-cash-stack"></i>
                        <span>Sales</span>
                    </a>

                    <div class="collapse" id="salesMenu">
                        <ul class="nav flex-column submenu">
                            <li>
                                <a class="nav-link text-white" href="{{ route('salesorders.index') }}">
                                    <i class="bi bi-receipt"></i>
                                    <span>Orders</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Inventories -->
                <li>
                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#inventoriesMenu">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventories</span>
                    </a>

                    <div class="collapse" id="inventoriesMenu">
                        <ul class="nav flex-column submenu">
                            <li>
                                <a class="nav-link text-white" href="{{ route('products.index') }}">
                                    <i class="bi bi-box"></i>
                                    <span>Products</span>
                                </a>
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
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
    </script>
</body>

</html>