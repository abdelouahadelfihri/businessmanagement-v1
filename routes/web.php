<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchaseOrderController;

Route::resource('suppliers', SupplierController::class);
Route::resource('purchase-requests', PurchaseRequestController::class);
Route::resource('purchase-orders', PurchaseOrderController::class);
