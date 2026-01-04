<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\Purchases\PurchaseOrder;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StockService;
class PurchaseReceiptController extends Controller
{
    public function index()
    {
        $receipts = PurchaseReceipt::latest()->paginate(15);
        return view('purchase_receipts.index', compact('receipts'));
    }
    public function create()
    {
        return view('purchase_receipts.create', ['purchaseOrders' => PurchaseOrder::with('lines.product')->get(), 'products' => Product::all()]);
    }
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $receipt = PurchaseReceipt::create([
                'warehouse_id' => $request->warehouse_id,
                'status' => 'draft'
            ]);

            foreach ($request->lines as $line) {
                $receipt->lines()->create($line);
            }
        });
    }

    public function post(PurchaseReceipt $receipt, StockService $stock)
    {
        abort_if($receipt->status !== 'draft', 400);

        DB::transaction(function () use ($receipt, $stock) {
            foreach ($receipt->lines as $line) {
                $stock->move(
                    $line->product_id,
                    $receipt->warehouse_id,
                    $line->quantity,
                    'in',
                    'purchase_receipt',
                    $receipt->id
                );
            }

            $receipt->update(['status' => 'posted']);
        });
    }

    public function cancel(PurchaseReceipt $receipt, StockService $stock)
    {
        abort_if($receipt->status !== 'posted', 400);

        DB::transaction(function () use ($receipt, $stock) {
            foreach ($receipt->lines as $line) {
                $stock->move(
                    $line->product_id,
                    $receipt->warehouse_id,
                    $line->quantity,
                    'out',
                    'purchase_receipt_cancel',
                    $receipt->id
                );
            }

            $receipt->update(['status' => 'cancelled']);
        });
    }
}