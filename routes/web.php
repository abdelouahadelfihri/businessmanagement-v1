<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Purchases\PurchaseRequestController;
use App\Http\Controllers\Purchases\PurchaseOrderController;
use App\Http\Controllers\Purchases\PurchaseReceiptController;
use App\Http\Controllers\Purchases\PurchaseInvoiceController;

use App\Http\Controllers\Sales\SaleQuotationController;
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
use App\Http\Controllers\MasterData\TransferController;

Route::middleware(['web'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/', fn() => view('dashboard'))->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('units', UnitController::class);
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('stocksmovements', StockMovementController::class);
    Route::resource('transfers', TransferController::class);

    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */
    Route::resource(
        'purchasesrequests',
        PurchaseRequestController::class
    )->parameters([
                'purchasesrequests' => 'purchaseRequest'
            ]);

    Route::resource('purchasesorders', PurchaseOrderController::class);
    Route::post('purchasesorders/{order}/post', [PurchaseOrderController::class, 'post'])->name('purchasesorders.post');
    Route::post('purchasesorders/{order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchasesorders.cancel');

    Route::resource('purchasesreceipts', PurchaseReceiptController::class);
    Route::resource('purchasesinvoices', PurchaseInvoiceController::class);

    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */
    Route::resource('salesquotations', SaleQuotationController::class);
    Route::resource('salesorders', SaleOrderController::class);
    Route::resource('salesdeliveries', DeliveryController::class);
    Route::resource('salesinvoices', SaleInvoiceController::class);
    Route::resource('salesreturns', SaleReturnController::class);

    Route::get('/stock/transfer', [StockMovementController::class, 'transferForm'])
        ->name('stocksmovements.transfer_form');

    Route::post('/stock/transfer', [StockMovementController::class, 'transfer'])
        ->name('stocksmovements.transfer');

    Route::resource('stock-movements', StockMovementController::class)
        ->except(['show', 'destroy']);

    // AJAX routes for modal add
    Route::post('suppliers/ajax-store', [SupplierController::class, 'ajaxStore'])->name('suppliers.ajaxStore');
    Route::post('categories/ajax-store', [CategoryController::class, 'ajaxStore'])->name('categories.ajaxStore');
    Route::post('units/ajax-store', [UnitController::class, 'ajaxStore'])->name('units.ajaxStore');
    Route::post('locations/ajax-store', [UnitController::class, 'ajaxStore'])->name('locations.ajaxStore');

});