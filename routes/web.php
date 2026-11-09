<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\CRM\ContactController;
use App\Http\Controllers\CRM\ProspectController;

use App\Http\Controllers\Purchases\PurchaseRequestController;
use App\Http\Controllers\Purchases\PurchaseOrderController;
use App\Http\Controllers\Purchases\PurchaseReceiptController;
use App\Http\Controllers\Purchases\PurchaseInvoiceController;

use App\Http\Controllers\Sales\SaleQuotationController;
use App\Http\Controllers\Sales\SaleOrderController;
use App\Http\Controllers\Sales\SaleDeliveryController;
use App\Http\Controllers\Sales\SaleInvoiceController;
use App\Http\Controllers\Sales\SaleReturnController;

use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\CategoryController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\WarehouseController;
use App\Http\Controllers\MasterData\LocationController;
use App\Http\Controllers\MasterData\StockMovementController;
use App\Http\Controllers\MasterData\TransferController;
use App\Http\Controllers\MasterData\StockController;

/*
|--------------------------------------------------------------------------
| Authentication (guests only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Application (signed-in, active users; every route is permission-checked
| by name, see config/permissions.php)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'active', 'permission'])->group(function () {

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::post('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::post('users/{user}/reset-link', [UserController::class, 'resetLink'])->name('users.reset_link');
        Route::resource('users', UserController::class);

        Route::resource('roles', RoleController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */
    Route::name('masterdata.')->group(function () {

        Route::resource('suppliers', SupplierController::class);
        Route::resource('customers', CustomerController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('units', UnitController::class);
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('locations', LocationController::class);
        Route::resource('stocksmovements', StockMovementController::class);
        Route::resource('transfers', TransferController::class);

    });

    /*
    |--------------------------------------------------------------------------
    | CRM
    |--------------------------------------------------------------------------
    */
    Route::name('crm.')->group(function () {

        Route::post('prospects/{prospect}/convert', [ProspectController::class, 'convert'])->name('prospects.convert');
        Route::post('prospects/{prospect}/status', [ProspectController::class, 'updateStatus'])->name('prospects.status');
        Route::resource('prospects', ProspectController::class);

        Route::post('contacts/{contact}/primary', [ContactController::class, 'makePrimary'])->name('contacts.primary');
        Route::resource('contacts', ContactController::class);

    });

    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */
    Route::name('purchases.')->group(function () {

        // Purchase requests
        Route::resource(
            'purchasesrequests',
            PurchaseRequestController::class
        )->parameters([
                    'purchasesrequests' => 'purchaseRequest'
                ]);

        Route::post('purchasesrequests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])
            ->name('purchasesrequests.approve');
        Route::post('purchasesrequests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])
            ->name('purchasesrequests.reject');

        // Purchase orders
        Route::post('purchasesorders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])
            ->name('purchasesorders.submit');
        Route::post('purchasesorders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
            ->name('purchasesorders.approve');
        Route::post('purchasesorders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])
            ->name('purchasesorders.send');
        Route::post('purchasesorders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
            ->name('purchasesorders.cancel');

        Route::resource(
            'purchasesorders',
            PurchaseOrderController::class
        )->parameters([
                    'purchasesorders' => 'purchaseOrder'
                ]);

        // Purchase receipts
        Route::post('purchasesreceipts/{purchaseReceipt}/validate', [PurchaseReceiptController::class, 'confirm'])
            ->name('purchasesreceipts.validate');
        Route::post('purchasesreceipts/{purchaseReceipt}/cancel', [PurchaseReceiptController::class, 'cancel'])
            ->name('purchasesreceipts.cancel');

        Route::resource(
            'purchasesreceipts',
            PurchaseReceiptController::class
        )->parameters([
                    'purchasesreceipts' => 'purchaseReceipt'
                ]);

        // Purchase invoices
        Route::post('purchasesinvoices/{purchaseInvoice}/validate', [PurchaseInvoiceController::class, 'confirm'])
            ->name('purchasesinvoices.validate');
        Route::post('purchasesinvoices/{purchaseInvoice}/cancel', [PurchaseInvoiceController::class, 'cancel'])
            ->name('purchasesinvoices.cancel');
        Route::post('purchasesinvoices/{purchaseInvoice}/payment', [PurchaseInvoiceController::class, 'payment'])
            ->name('purchasesinvoices.payment');

        Route::resource(
            'purchasesinvoices',
            PurchaseInvoiceController::class
        )->parameters([
                    'purchasesinvoices' => 'purchaseInvoice'
                ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */
    Route::name('sales.')->group(function () {

        // Sales quotations
        Route::post('salesquotations/{saleQuotation}/send', [SaleQuotationController::class, 'send'])
            ->name('salesquotations.send');
        Route::post('salesquotations/{saleQuotation}/accept', [SaleQuotationController::class, 'accept'])
            ->name('salesquotations.accept');
        Route::post('salesquotations/{saleQuotation}/reject', [SaleQuotationController::class, 'reject'])
            ->name('salesquotations.reject');
        Route::post('salesquotations/{saleQuotation}/cancel', [SaleQuotationController::class, 'cancel'])
            ->name('salesquotations.cancel');

        Route::resource(
            'salesquotations',
            SaleQuotationController::class
        )->parameters([
                    'salesquotations' => 'saleQuotation'
                ]);

        // Sales orders
        Route::get('salesorders/from-quotation/{saleQuotation}', [SaleOrderController::class, 'createFromQuotation'])
            ->name('salesorders.from_quotation');
        Route::post('salesorders/{saleOrder}/confirm', [SaleOrderController::class, 'confirm'])
            ->name('salesorders.confirm');
        Route::post('salesorders/{saleOrder}/process', [SaleOrderController::class, 'process'])
            ->name('salesorders.process');
        Route::post('salesorders/{saleOrder}/deliver', [SaleOrderController::class, 'deliver'])
            ->name('salesorders.deliver');
        Route::post('salesorders/{saleOrder}/cancel', [SaleOrderController::class, 'cancel'])
            ->name('salesorders.cancel');

        Route::resource(
            'salesorders',
            SaleOrderController::class
        )->parameters([
                    'salesorders' => 'saleOrder'
                ]);

        // Sales deliveries
        Route::post('salesdeliveries/{saleDelivery}/validate', [SaleDeliveryController::class, 'confirm'])
            ->name('salesdeliveries.validate');
        Route::post('salesdeliveries/{saleDelivery}/cancel', [SaleDeliveryController::class, 'cancel'])
            ->name('salesdeliveries.cancel');

        Route::resource(
            'salesdeliveries',
            SaleDeliveryController::class
        )->parameters([
                    'salesdeliveries' => 'saleDelivery'
                ]);

        // Sales invoices
        Route::post('salesinvoices/{saleInvoice}/validate', [SaleInvoiceController::class, 'issueInvoice'])
            ->name('salesinvoices.validate');
        Route::post('salesinvoices/{saleInvoice}/payment', [SaleInvoiceController::class, 'recordPayment'])
            ->name('salesinvoices.payment');
        Route::post('salesinvoices/{saleInvoice}/cancel', [SaleInvoiceController::class, 'cancel'])
            ->name('salesinvoices.cancel');

        Route::resource(
            'salesinvoices',
            SaleInvoiceController::class
        )->parameters([
                    'salesinvoices' => 'saleInvoice'
                ]);

        // Sales returns
        Route::post('salesreturns/{saleReturn}/validate', [SaleReturnController::class, 'validateReturn'])
            ->name('salesreturns.validate');
        Route::post('salesreturns/{saleReturn}/cancel', [SaleReturnController::class, 'cancel'])
            ->name('salesreturns.cancel');

        Route::resource(
            'salesreturns',
            SaleReturnController::class
        )->parameters([
                    'salesreturns' => 'saleReturn'
                ]);

    });

    Route::get('/stock/transfer', [StockMovementController::class, 'transferForm'])
        ->name('stocksmovements.transfer_form');

    Route::post('/stock/transfer', [StockMovementController::class, 'transfer'])
        ->name('stocksmovements.transfer');

    Route::resource('stock-movements', StockMovementController::class)
        ->except(['show', 'destroy']);

    // AJAX routes for modal add
    Route::post('suppliers/ajax-store', [SupplierController::class, 'ajaxStore'])->name('suppliers.ajaxStore');
    Route::post('customers/ajax-store', [CustomerController::class, 'ajaxStore'])->name('customers.ajaxStore');
    Route::post('categories/ajax-store', [CategoryController::class, 'ajaxStore'])->name('categories.ajaxStore');
    Route::post('units/ajax-store', [UnitController::class, 'ajaxStore'])->name('units.ajaxStore');
    Route::post('locations/ajax-store', [UnitController::class, 'ajaxStore'])->name('locations.ajaxStore');

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
});
