<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchases\SupplierController;
use App\Http\Controllers\Purchases\PurchaseRequestController;
use App\Http\Controllers\Purchases\PurchaseOrderController;

Route::resource('suppliers', SupplierController::class);
Route::resource('purchase-requests', PurchaseRequestController::class);
Route::resource('purchase-orders', PurchaseOrderController::class);
Route::get('/', function () {
    return view('dashboard'); // or the correct dashboard view file
})->name('dashboard');