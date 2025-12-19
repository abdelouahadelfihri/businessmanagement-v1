<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Purchases\PurchaseRequestController;
use App\Http\Controllers\Purchases\PurchaseOrderController;
use App\Http\Controllers\Purchases\PurchaseReceiptController;
use App\Http\Controllers\Purchases\PurchaseInvoiceController;

use App\Http\Controllers\Sales\SaleQuoteController;
use App\Http\Controllers\Sales\SaleOrderController;
use App\Http\Controllers\Sales\DeliveryController;
use App\Http\Controllers\Sales\SaleInvoiceController;
use App\Http\Controllers\Sales\SaleReturnController;


use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\InventoryController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\CategoryController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\WarehouseController;
use App\Http\Controllers\MasterData\LocationController;
use App\Http\Controllers\MasterData\StockMovementController;

Route::resource('suppliers', SupplierController::class);
Route::resource('purchasesrequests', PurchaseRequestController::class);
Route::resource('purchasesorders', PurchaseOrderController::class);
Route::resource('purchasesreceipts', PurchaseReceiptController::class);
Route::resource('purchasesinvoices', PurchaseInvoiceController::class);

Route::resource('customers', CustomerController::class);
Route::resource('salesquotations', SaleQuoteController::class);
Route::resource('salesorders', SaleOrderController::class);
Route::resource('salesdeliveries', DeliveryController::class);
Route::resource('salesinvoices', SaleInvoiceController::class);
Route::resource('salesreturns', SaleReturnController::class);

Route::resource('categories', CategoryController::class);
Route::resource('inventories', InventoryController::class);
Route::resource('products', ProductController::class);
Route::resource('units', UnitController::class);
Route::resource('warehouses', WarehouseController::class);
Route::resource('locations', LocationController::class);
Route::resource('stocksmovements', StockMovementController::class);

Route::get('/', function () {
    return view('dashboard'); // or the correct dashboard view file
})->name('dashboard');