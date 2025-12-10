<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchases\SupplierController;
use App\Http\Controllers\Purchases\PurchaseRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home route (optional redirect to purchase requests)
Route::get('/', function() {
    return redirect()->route('purchase-requests.index');
});

// ---------------- SUPPLIERS ----------------

// Standard resource routes
Route::resource('suppliers', SupplierController::class);

// Quick store for modal AJAX (PurchaseRequest form)
Route::post('suppliers/quick-store', [SupplierController::class, 'storeQuick'])
    ->name('suppliers.storeQuick');

// ---------------- PURCHASE REQUESTS ----------------

// Standard resource routes
Route::resource('purchase-requests', PurchaseRequestController::class);