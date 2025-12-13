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
    </style>
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

                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
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
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchaseRequestsMenu">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Purchase Requests</span>
                                </a>

                                <div class="collapse" id="purchaseRequestsMenu">
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
                                <a class="nav-link" data-bs-toggle="collapse" href="#purchaseOrdersMenu">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Purchase Orders</span>
                                </a>

                                <div class="collapse" id="purchaseOrdersMenu">
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
                                <a class="nav-link" href="{{ route('salesorders.index') }}">
                                    <i class="bi bi-receipt"></i>
                                    <span>Orders</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Inventories -->
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#inventoriesMenu">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventories</span>
                    </a>

                    <div class="collapse" id="inventoriesMenu">
                        <ul class="nav flex-column submenu">
                            <li>
                                <a class="nav-link" href="{{ route('products.index') }}">
                                    <i class="bi bi-box"></i>
                                    <span>Products</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </nav>

        <!-- CONTENT -->
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