<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchases\SupplierController;
use App\Http\Controllers\Purchases\PurchaseRequestController;

// Redirect home to purchase request list
Route::get('/', function () {
    return redirect()->route('purchase-requests.index');
});

// ---------------- SUPPLIERS ----------------
Route::resource('suppliers', SupplierController::class);
Route::post('suppliers/quick-store', [SupplierController::class, 'storeQuick'])
    ->name('suppliers.storeQuick');

// ---------------- PURCHASE REQUESTS ----------------
Route::resource('purchase-requests', PurchaseRequestController::class);