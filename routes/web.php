<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// -------------------------------------------------------
// 🏠 DASHBOARD
// -------------------------------------------------------
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');


// -------------------------------------------------------
// 🛒 PURCHASES MODULE
// -------------------------------------------------------
use App\Http\Controllers\Purchases\SupplierController;
use App\Http\Controllers\Purchases\PurchaseRequestController;

Route::prefix('purchases')->name('purchases.')->group(function () {

    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/quick-store', [SupplierController::class, 'storeQuick'])
        ->name('suppliers.storeQuick');

    // Purchase Requests
    Route::resource('requests', PurchaseRequestController::class);
});


// -------------------------------------------------------
// 🧾 SALES MODULE
// -------------------------------------------------------
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\Sales\SalesQuoteController;
use App\Http\Controllers\Sales\SalesInvoiceController;

Route::prefix('sales')->name('sales.')->group(function () {

    Route::resource('customers', CustomerController::class);
    Route::resource('quotations', SalesQuoteController::class);
    Route::resource('invoices', SalesInvoiceController::class);
});


// -------------------------------------------------------
// 📦 INVENTORY MODULE
// -------------------------------------------------------
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\CategoryController;
//use App\Http\Controllers\MasterData\StockMovementController;

Route::prefix('inventory')->name('inventory.')->group(function () {

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    //Route::resource('stock-movements', StockMovementController::class);
});


// -------------------------------------------------------
// ⚙️ SETTINGS (Users & Roles)
// -------------------------------------------------------
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\RoleController;

Route::prefix('settings')->name('settings.')->group(function () {

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
});