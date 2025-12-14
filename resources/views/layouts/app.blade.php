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

        /* Layout */
        .layout {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 230px;
            height: 100vh;
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

        /* Brand */
        .app-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            white-space: nowrap;
            margin-bottom: 1.5rem;
        }

        .sidebar.collapsed .app-brand span {
            display: none;
        }

        /* Toggle button */
        .toggle-btn {
            background: rgba(255, 255, 255, 0.5);
            border: none;
            color: #000;
            border-radius: 10px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        /* Topbar holding toggle */
        .topbar {
            position: fixed;
            top: 10px;
            left: 230px;
            z-index: 1050;
            transition: left 0.3s;
        }

        .sidebar.collapsed ~ .topbar {
            left: 72px;
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
        <nav class="sidebar p-3" id="sidebar">

            <div class="app-brand">
                <i class="bi bi-building"></i>
                <span>Business MS</span>
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
                                <a class="nav-link" href="{{ route('purchasesrequests.index') }}">
                                    <i class="bi bi-list-ul"></i>
                                    <span>Purchase Requests</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link" href="{{ route('purchasesorders.index') }}">
                                    <i class="bi bi-bag-check"></i>
                                    <span>Purchase Orders</span>
                                </a>
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
                                <a class="nav-link">
                                    <i class="bi bi-receipt"></i>
                                    <span>Invoices</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Master Data -->
                <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#masterDataMenu">
                        <i class="bi bi-box-seam"></i>
                        <span>Master Data</span>
                    </a>
                    <div class="collapse" id="masterDataMenu">
                        <ul class="nav flex-column submenu">
                            <li><a class="nav-link" href="{{ route('categories.index') }}"><span>Categories</span></a></li>
                            <li><a class="nav-link" href="{{ route('products.index') }}"><span>Products</span></a></li>
                            <li><a class="nav-link" href="{{ route('units.index') }}"><span>Units</span></a></li>
                            <li><a class="nav-link" href="{{ route('warehouses.index') }}"><span>Warehouses</span></a></li>
                        </ul>
                    </div>
                </li>

            </ul>
        </nav>

        <!-- TOGGLE BUTTON OUTSIDE SIDEBAR -->
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
    </script>

</body>

</html>