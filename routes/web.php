<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', fn() => view('dashboard'))->name('dashboard');

// Purchases
Route::prefix('purchases')->group(function () {
    Route::get('requests', fn() => view('purchases.requests.list'))->name('purchases.requests.list');
    Route::get('requests/add', fn() => view('purchases.requests.add'))->name('purchases.requests.add');

    Route::get('orders', fn() => view('purchases.orders.list'))->name('purchases.orders.list');
    Route::get('orders/add', fn() => view('purchases.orders.add'))->name('purchases.orders.add');
    Route::get('purchases/orders/edit/{order}', function ($orderId) {
        $order = \App\Models\Purchases\PurchaseOrder::findOrFail($orderId);
        return view('purchases.orders.edit', compact('order'));
    })->name('purchases.orders.edit');
});

// Sales
Route::prefix('sales')->group(function () {
    Route::get('orders', fn() => view('sales.orders.list'))->name('sales.orders.list');
    Route::get('orders/add', fn() => view('sales.orders.add'))->name('sales.orders.add');
});

// Inventories
Route::prefix('inventories')->group(function () {
    Route::get('products', fn() => view('inventories.products.list'))->name('inventories.products.list');
    Route::get('products/add', fn() => view('inventories.products.add'))->name('inventories.products.add');
});

/*
|--------------------------------------------------------------------------
| Purchase Requests Add/Edit
|--------------------------------------------------------------------------
*/
Route::get('/requests/create', function () {
    return view('requests.create');
})->name('requests.create');

Route::get('/requests/{id}/edit', function ($id) {
    $request = PurchaseRequest::findOrFail($id);
    return view('requests.edit', compact('request'));
})->name('requests.edit');


/*
|--------------------------------------------------------------------------
| Supplier Selection Page
|--------------------------------------------------------------------------
*/
Route::get('/suppliers/select', function () {
    $suppliers = Supplier::all();
    $redirect = request('redirect');
    $id = request('id');
    return view('suppliers.select', compact('suppliers', 'redirect', 'id'));
})->name('suppliers.select');


/*
|--------------------------------------------------------------------------
| Supplier Add Page
|--------------------------------------------------------------------------
*/
Route::get('/suppliers/create', function () {
    $redirect = request('redirect');
    $id = request('id');
    return view('suppliers.create', compact('redirect', 'id'));
})->name('suppliers.create');