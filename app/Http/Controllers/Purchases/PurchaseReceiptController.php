<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Product;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\Purchases\PurchaseReceiptLine;
use App\Traits\StockHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseReceiptController extends Controller
{
    use StockHelper;

    public function index(Request $request)
    {
        $purchaseReceipts = PurchaseReceipt::with(['supplier', 'purchaseOrder', 'warehouse'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('receipt_number', 'like', $term)
                        ->orWhere('delivery_note_number', 'like', $term)
                        ->orWhereHas('purchaseOrder', fn ($po) => $po->where('po_number', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('quality_status'), fn ($q) => $q->where('quality_status', $request->quality_status))
            ->latest('date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('purchases.purchasesreceipts.index', compact('purchaseReceipts'));
    }

    public function create(Request $request)
    {
        $prefill = [];
        $prefillLines = null;

        if ($request->filled('purchase_order_id')) {
            $order = PurchaseOrder::with('items')->find($request->purchase_order_id);

            if ($order) {
                $prefill = [
                    'purchase_order_id' => $order->id,
                    'supplier_id'       => $order->supplier_id,
                    'currency'          => $order->currency,
                ];

                $prefillLines = $order->items->map(function ($line) {
                    $alreadyReceived = (float) PurchaseReceiptLine::where('purchase_order_line_id', $line->id)
                        ->whereHas('receipt', fn ($q) => $q->where('status', 'validated'))
                        ->sum('received_quantity');

                    $remaining = max((float) $line->quantity - $alreadyReceived, 0);

                    return [
                        'purchase_order_line_id' => $line->id,
                        'product_id'             => $line->product_id,
                        'description'            => $line->description,
                        'ordered_quantity'       => (float) $line->quantity,
                        'received_quantity'      => $remaining,
                        'rejected_quantity'      => 0,
                        'unit'                   => $line->unit,
                        'unit_price'             => (float) $line->unit_price,
                        'discount_percent'       => (float) ($line->discount_percent ?? 0),
                        'tax_rate'               => (float) ($line->tax_rate ?? 0),
                        'batch_number'           => '',
                        'expiry_date'            => '',
                    ];
                })->filter(fn ($l) => $l['received_quantity'] > 0)->values()->all() ?: null;
            }
        }

        return view('purchases.purchasesreceipts.create', $this->formData() + compact('prefill', 'prefillLines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $receipt = DB::transaction(fn () => $this->persist(new PurchaseReceipt(), $data));

        if ($request->boolean('validate_now')) {
            return $this->confirm($receipt);
        }

        return redirect()->route('purchases.purchasesreceipts.index')
            ->with('success', 'Purchase receipt ' . $receipt->receipt_number . ' created.');
    }

    public function show(PurchaseReceipt $purchaseReceipt)
    {
        $purchaseReceipt->load(['supplier', 'purchaseOrder', 'warehouse', 'items.product', 'receivedBy', 'validatedBy']);

        return view('purchases.purchasesreceipts.show', compact('purchaseReceipt'));
    }

    public function edit(PurchaseReceipt $purchaseReceipt)
    {
        if ($purchaseReceipt->status !== 'draft') {
            return redirect()->route('purchases.purchasesreceipts.show', $purchaseReceipt)
                ->with('error', 'Only draft receipts can be edited.');
        }

        $purchaseReceipt->load('items');

        return view('purchases.purchasesreceipts.edit', $this->formData($purchaseReceipt->purchase_order_id) + compact('purchaseReceipt'));
    }

    public function update(Request $request, PurchaseReceipt $purchaseReceipt)
    {
        if ($purchaseReceipt->status !== 'draft') {
            return redirect()->route('purchases.purchasesreceipts.show', $purchaseReceipt)
                ->with('error', 'Only draft receipts can be edited.');
        }

        $data = $request->validate($this->rules());

        DB::transaction(fn () => $this->persist($purchaseReceipt, $data));

        if ($request->boolean('validate_now')) {
            return $this->confirm($purchaseReceipt);
        }

        return redirect()->route('purchases.purchasesreceipts.index')
            ->with('success', 'Purchase receipt ' . $purchaseReceipt->receipt_number . ' updated.');
    }

    public function destroy(PurchaseReceipt $purchaseReceipt)
    {
        if (!in_array($purchaseReceipt->status, ['draft', 'cancelled'], true)) {
            return back()->with('error', 'Only draft or cancelled receipts can be deleted.');
        }

        DB::transaction(function () use ($purchaseReceipt) {
            $purchaseReceipt->items()->delete();
            $purchaseReceipt->delete();
        });

        return redirect()->route('purchases.purchasesreceipts.index')
            ->with('success', 'Purchase receipt deleted.');
    }

    /**
     * Validate a draft receipt (named "confirm" because Controller already has a validate() method).
     */
    public function confirm(PurchaseReceipt $purchaseReceipt)
    {
        if ($purchaseReceipt->status !== 'draft') {
            return back()->with('error', 'Only draft receipts can be validated.');
        }

        DB::transaction(function () use ($purchaseReceipt) {
            $purchaseReceipt->update([
                'status'       => 'validated',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            $this->postStockMovements($purchaseReceipt);

            if ($purchaseReceipt->purchaseOrder) {
                $this->syncOrderStatus($purchaseReceipt->purchaseOrder);
            }
        });

        return redirect()->route('purchases.purchasesreceipts.show', $purchaseReceipt)
            ->with('success', 'Purchase receipt validated.');
    }

    public function cancel(PurchaseReceipt $purchaseReceipt)
    {
        if ($purchaseReceipt->status !== 'draft') {
            return back()->with('error', 'Only draft receipts can be cancelled.');
        }

        $purchaseReceipt->update(['status' => 'cancelled']);

        return redirect()->route('purchases.purchasesreceipts.show', $purchaseReceipt)
            ->with('success', 'Purchase receipt cancelled.');
    }

    // ------------------------------------------------------------------ helpers

    private function formData(?int $currentOrderId = null): array
    {
        return [
            'suppliers'      => Supplier::orderBy('name')->get(),
            'warehouses'     => Warehouse::orderBy('name')->get(),
            'products'       => Product::orderBy('name')->get(),
            'purchaseOrders' => PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received'])
                ->when($currentOrderId, fn ($q) => $q->orWhere('id', $currentOrderId))
                ->orderByDesc('id')
                ->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'purchase_order_id'    => ['required', 'exists:purchase_orders,id'],
            'supplier_id'          => ['required', 'exists:suppliers,id'],
            'warehouse_id'         => ['required', 'exists:warehouses,id'],
            'date'                 => ['required', 'date'],
            'delivery_note_number' => ['nullable', 'string', 'max:100'],
            'carrier'              => ['nullable', 'string', 'max:150'],
            'tracking_number'      => ['nullable', 'string', 'max:150'],
            'currency'             => ['required', Rule::in(['MAD', 'EUR', 'USD'])],
            'quality_status'       => ['required', Rule::in(array_keys(PurchaseReceipt::QUALITY_STATUSES))],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'        => ['nullable', 'numeric', 'min:0'],
            'notes'                => ['nullable', 'string'],
            'internal_notes'       => ['nullable', 'string'],

            'items'                          => ['required', 'array', 'min:1'],
            'items.*.purchase_order_line_id' => ['nullable', 'integer'],
            'items.*.product_id'             => ['nullable', 'integer'],
            'items.*.description'            => ['required', 'string', 'max:255'],
            'items.*.ordered_quantity'       => ['nullable', 'numeric', 'min:0'],
            'items.*.received_quantity'      => ['required', 'numeric', 'min:0'],
            'items.*.rejected_quantity'      => ['nullable', 'numeric', 'min:0'],
            'items.*.unit'                   => ['nullable', 'string', 'max:30'],
            'items.*.unit_price'             => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent'       => ['nullable', 'numeric', 'between:0,100'],
            'items.*.tax_rate'               => ['nullable', 'numeric', 'between:0,100'],
            'items.*.batch_number'           => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date'            => ['nullable', 'date'],
        ];
    }

    private function persist(PurchaseReceipt $receipt, array $data): PurchaseReceipt
    {
        [$lines, $subtotal, $tax] = $this->buildLines($data['items']);

        $discount = (float) ($data['discount_amount'] ?? 0);
        $shipping = (float) ($data['shipping_cost'] ?? 0);

        $receipt->fill([
            'purchase_order_id'    => $data['purchase_order_id'],
            'supplier_id'          => $data['supplier_id'],
            'warehouse_id'         => $data['warehouse_id'],
            'date'                 => $data['date'],
            'delivery_note_number' => $data['delivery_note_number'] ?? null,
            'carrier'              => $data['carrier'] ?? null,
            'tracking_number'      => $data['tracking_number'] ?? null,
            'currency'             => $data['currency'],
            'quality_status'       => $data['quality_status'],
            'notes'                => $data['notes'] ?? null,
            'internal_notes'       => $data['internal_notes'] ?? null,
            'subtotal'             => $subtotal,
            'discount_amount'      => $discount,
            'tax_amount'           => $tax,
            'shipping_cost'        => $shipping,
            'total'                => round($subtotal - $discount + $tax + $shipping, 2),
        ]);

        if (!$receipt->exists) {
            $receipt->status = 'draft';
            $receipt->received_by = Auth::id();
        }

        $receipt->save();

        $receipt->items()->delete();
        $receipt->items()->createMany($lines);

        return $receipt;
    }

    private function buildLines(array $items): array
    {
        $subtotal = 0;
        $tax = 0;
        $lines = [];

        foreach ($items as $item) {
            $qty   = (float) $item['received_quantity'];
            $price = (float) $item['unit_price'];
            $disc  = (float) ($item['discount_percent'] ?? 0);
            $rate  = (float) ($item['tax_rate'] ?? 0);
            $total = round($qty * $price * (1 - $disc / 100), 2);

            $subtotal += $total;
            $tax += $total * $rate / 100;

            $lines[] = [
                'purchase_order_line_id' => ($item['purchase_order_line_id'] ?? null) ?: null,
                'product_id'             => ($item['product_id'] ?? null) ?: null,
                'description'            => $item['description'],
                'unit'                   => $item['unit'] ?? null,
                'ordered_quantity'       => (float) ($item['ordered_quantity'] ?? 0),
                'received_quantity'      => $qty,
                'rejected_quantity'      => (float) ($item['rejected_quantity'] ?? 0),
                'unit_price'             => $price,
                'discount_percent'       => $disc,
                'tax_rate'               => $rate,
                'total_price'            => $total,
                'batch_number'           => $item['batch_number'] ?? null,
                'expiry_date'            => ($item['expiry_date'] ?? null) ?: null,
            ];
        }

        return [$lines, round($subtotal, 2), round($tax, 2)];
    }

    /**
     * Move the purchase order to "partially_received" or "received"
     * depending on the validated receipts linked to its lines.
     */
    private function syncOrderStatus(PurchaseOrder $order): void
    {
        if (!in_array($order->status, ['approved', 'sent', 'partially_received'], true)) {
            return;
        }

        $order->loadMissing('items');

        $fullyReceived = $order->items->every(function ($line) {
            $received = (float) PurchaseReceiptLine::where('purchase_order_line_id', $line->id)
                ->whereHas('receipt', fn ($q) => $q->where('status', 'validated'))
                ->sum('received_quantity');

            return $received >= (float) $line->quantity;
        });

        $order->update(['status' => $fullyReceived ? 'received' : 'partially_received']);
    }

    /**
     * Post stock for a validated receipt, same pattern as the previous controller:
     * one StockMovement row + updateStock() per line.
     * Accepted quantity = received - rejected.
     */
    private function postStockMovements(PurchaseReceipt $receipt): void
    {
        $receipt->loadMissing('items');

        foreach ($receipt->items as $line) {
            $accepted = (float) $line->received_quantity - (float) $line->rejected_quantity;

            if (!$line->product_id || $accepted <= 0) {
                continue;
            }

            StockMovement::create([
                'product_id'   => $line->product_id,
                'warehouse_id' => $receipt->warehouse_id,
                'type'         => 'in',
                'quantity'     => $accepted,
                'reason'       => 'purchase_receipt',
                'date'         => now(),
                'source_type'  => PurchaseReceipt::class,
                'source_id'    => $receipt->id,
            ]);

            $this->updateStock($line->product_id, $receipt->warehouse_id, $accepted, 'in');
        }
    }
}
