<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseRequestController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

/*
| Suppliers
*/
Route::resource('suppliers', SupplierController::class);

/*
| Purchase Requests
*/
Route::resource('purchase-requests', PurchaseRequestController::class);

/*
| AJAX endpoint to create supplier quickly from modal
*/
Route::post('/suppliers-quick', [SupplierController::class, 'storeQuick'])->name('suppliers.storeQuick');




Route::post('suppliers/quick-store', [SupplierController::class,'storeQuick'])->name('suppliers.storeQuick');