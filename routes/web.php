<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchases\SupplierController;
use App\Http\Controllers\Purchases\PurchaseRequestController;
use App\Http\Controllers\Purchases\PurchaseOrderController;
use App\Http\Controllers\Purchases\PurchaseReceiptController;
use App\Http\Controllers\Purchases\PurchaseInvoiceController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\MasterData\InventoryController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\CategoryController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\WarehouseController;


Route::resource('suppliers', SupplierController::class);
Route::resource('purchasesrequests', PurchaseRequestController::class);
Route::resource('purchasesorders', PurchaseOrderController::class);
Route::resource('purchasesreceipts', PurchaseReceiptController::class);
Route::resource('purchasesinvoices', PurchaseInvoiceController::class);

Route::resource('salesorders', SalesOrderController::class);

Route::resource('categories', CategoryController::class);
Route::resource('inventories', InventoryController::class);
Route::resource('products', ProductController::class);
Route::resource('units', UnitController::class);
Route::resource('warehouses', WarehouseController::class);

Route::get('/', function () {
    return view('dashboard'); // or the correct dashboard view file
})->name('dashboard');