<div class="sidebar">
    <h4 class="text-center mb-4">Business Management</h4>

    {{-- Purchases --}}
    <a data-bs-toggle="collapse" href="#purchasesMenu" role="button" aria-expanded="false">
        📦 Purchases
    </a>
    <div class="collapse" id="purchasesMenu">
        <a class="ms-3" data-bs-toggle="collapse" href="#purchaseRequestsMenu">
            • Purchase Requests
        </a>
        <div class="collapse" id="purchaseRequestsMenu">
            <a class="ms-4" href="{{ route('purchase_requests.index') }}">List</a>
            <a class="ms-4" href="{{ route('purchase_requests.create') }}">Add</a>
        </div>

        <a class="ms-3" data-bs-toggle="collapse" href="#purchaseOrdersMenu">
            • Purchase Orders
        </a>
        <div class="collapse" id="purchaseOrdersMenu">
            <a class="ms-4" href="{{ route('purchase_orders.index') }}">List</a>
            <a class="ms-4" href="{{ route('purchase_orders.create') }}">Add</a>
        </div>

        <a class="ms-3" data-bs-toggle="collapse" href="#suppliersMenu">
            • Suppliers
        </a>
        <div class="collapse" id="suppliersMenu">
            <a class="ms-4" href="{{ route('suppliers.index') }}">List</a>
            <a class="ms-4" href="{{ route('suppliers.create') }}">Add</a>
        </div>
    </div>


    {{-- Sales --}}
    <a data-bs-toggle="collapse" href="#salesMenu" role="button" aria-expanded="false">
        💰 Sales
    </a>
    <div class="collapse" id="salesMenu">
        <a class="ms-3" data-bs-toggle="collapse" href="#quotationsMenu">
            • Quotations
        </a>
        <div class="collapse" id="quotationsMenu">
            <a class="ms-4" href="{{ route('quotations.index') }}">List</a>
            <a class="ms-4" href="{{ route('quotations.create') }}">Add</a>
        </div>

        <a class="ms-3" data-bs-toggle="collapse" href="#salesOrdersMenu">
            • Sales Orders
        </a>
        <div class="collapse" id="salesOrdersMenu">
            <a class="ms-4" href="{{ route('sales_orders.index') }}">List</a>
            <a class="ms-4" href="{{ route('sales_orders.create') }}">Add</a>
        </div>

        <a class="ms-3" data-bs-toggle="collapse" href="#customersMenu">
            • Customers
        </a>
        <div class="collapse" id="customersMenu">
            <a class="ms-4" href="{{ route('customers.index') }}">List</a>
            <a class="ms-4" href="{{ route('customers.create') }}">Add</a>
        </div>
    </div>


    {{-- Inventories --}}
    <a data-bs-toggle="collapse" href="#inventoryMenu" role="button" aria-expanded="false">
        🏭 Inventories
    </a>
    <div class="collapse" id="inventoryMenu">

        <a class="ms-3" data-bs-toggle="collapse" href="#itemsMenu">
            • Items
        </a>
        <div class="collapse" id="itemsMenu">
            <a class="ms-4" href="{{ route('items.index') }}">List</a>
            <a class="ms-4" href="{{ route('items.create') }}">Add</a>
        </div>

        <a class="ms-3" data-bs-toggle="collapse" href="#stockMenu">
            • Stock
        </a>
        <div class="collapse" id="stockMenu">
            <a class="ms-4" href="{{ route('stocks.index') }}">List</a>
            <a class="ms-4" href="{{ route('stocks.create') }}">Add</a>
        </div>

        <a class="ms-3" data-bs-toggle="collapse" href="#warehousesMenu">
            • Warehouses
        </a>
        <div class="collapse" id="warehousesMenu">
            <a class="ms-4" href="{{ route('warehouses.index') }}">List</a>
            <a class="ms-4" href="{{ route('warehouses.create') }}">Add</a>
        </div>

    </div>
</div>