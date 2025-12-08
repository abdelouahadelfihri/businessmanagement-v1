<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ---------------------
// Sanctum default user route
// ---------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ---------------------
// Purchases Controllers
// ---------------------
use App\Http\Controllers\Purchases\SupplierController;
use App\Http\Controllers\Purchases\CustomerController;
use App\Http\Controllers\Purchases\PurchaseOrderController;
use App\Http\Controllers\Purchases\PurchaseReceiptController;
use App\Http\Controllers\Purchases\PurchaseInvoiceController;
use App\Http\Controllers\Purchases\PurchaseRequestController;

// Purchases Routes
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('purchase-orders', PurchaseOrderController::class);
Route::apiResource('purchase-receipts', PurchaseReceiptController::class);
Route::apiResource('purchase-invoices', PurchaseInvoiceController::class);
Route::apiResource('purchase-requests', PurchaseRequestController::class);
// ---------------------
// Sales Controllers
// ---------------------
use App\Http\Controllers\Sales\SalesQuoteController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesDeliveryController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\SalesReturnController;
use App\Http\Controllers\Sales\SalesReturnLineController;
use App\Http\Controllers\Sales\DeliveryController;
use App\Http\Controllers\Sales\DeliveryLineController;

// Sales Routes
Route::apiResource('sales-quotes', SalesQuoteController::class);
Route::apiResource('sales-orders', SalesOrderController::class);
Route::apiResource('sales-deliveries', SalesDeliveryController::class);
Route::apiResource('sales-invoices', SalesInvoiceController::class);
Route::apiResource('sales-returns', SalesReturnController::class);
Route::apiResource('sales-return-lines', SalesReturnLineController::class);
Route::apiResource('deliveries', DeliveryController::class);
Route::apiResource('delivery-lines', DeliveryLineController::class);

// ---------------------
// MasterData Controllers
// ---------------------
use App\Http\Controllers\MasterData\CategoryController;
use App\Http\Controllers\MasterData\InventoryController;
use App\Http\Controllers\MasterData\MeasurementUnitController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\LocationController;
use App\Http\Controllers\MasterData\TransferController;
use App\Http\Controllers\MasterData\WarehouseController;

// MasterData Routes

Route::apiResource('categories', CategoryController::class);
Route::apiResource('inventories', InventoryController::class);
Route::apiResource('measurement-units', MeasurementUnitController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('locations', LocationController::class);
Route::apiResource('transfers', TransferController::class);
Route::apiResource('warehouses', WarehouseController::class);