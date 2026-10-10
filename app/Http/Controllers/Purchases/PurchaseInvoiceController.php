<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Product;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseInvoiceLine;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\PurchaseReceipt;
use App\Traits\StockHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseInvoiceController extends Controller
{
    use StockHelper;

    public function index(Request $request)
    {
        $purchaseInvoices = PurchaseInvoice::with(['supplier', 'purchaseOrder'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('invoice_number', 'like', $term)
                        ->orWhere('supplier_invoice_number', 'like', $term)
                        ->orWhereHas('purchaseOrder', fn ($po) => $po->where('po_number', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->latest('date')
            ->latest('id')
            ->paginate(15)
            ->appends($request->query());

        return view('purchases.purchasesinvoices.index', compact('purchaseInvoices'));
    }

    public function create(Request $request)
    {
        $prefill = [];
        $prefillLines = null;

        if ($request->filled('purchase_receipt_id')) {
            $receipt = PurchaseReceipt::with(['items', 'purchaseOrder'])->find($request->purchase_receipt_id);

            if ($receipt) {
                $prefill = [
                    'purchase_receipt_id' => $receipt->id,
                    'purchase_order_id'   => $receipt->purchase_order_id,
                    'supplier_id'         => $receipt->supplier_id,
                    'currency'            => $receipt->currency,
                    'payment_terms'       => optional($receipt->purchaseOrder)->payment_terms,
                ];

                // Invoice what was accepted: received - rejected
                $prefillLines = collect($receipt->items)->map(function ($l) {
                    return [
                        'purchase_order_line_id' => $l->purchase_order_line_id,
                        'product_id'             => $l->product_id,
                        'description'            => $l->description,
                        'quantity'               => max((float) $l->received_quantity - (float) $l->rejected_quantity, 0),
                        'unit'                   => $l->unit,
                        'price'                  => (float) $l->unit_price,
                        'discount_percent'       => (float) $l->discount_percent,
                        'tax_rate'               => (float) $l->tax_rate,
                    ];
                })->filter(fn ($l) => $l['quantity'] > 0)->values()->all() ?: null;
            }
        } elseif ($request->filled('purchase_order_id')) {
            $order = PurchaseOrder::with('items')->find($request->purchase_order_id);

            if ($order) {
                $prefill = [
                    'purchase_order_id' => $order->id,
                    'supplier_id'       => $order->supplier_id,
                    'currency'          => $order->currency,
                    'payment_terms'     => $order->payment_terms,
                ];

                // Invoice what is still not invoiced on the order
                $prefillLines = collect($order->items)->map(function ($l) {
                    $invoiced = (float) PurchaseInvoiceLine::where('purchase_order_line_id', $l->id)
                        ->whereHas('invoice', fn ($q) => $q->where('status', 'validated'))
                        ->sum('quantity');

                    return [
                        'purchase_order_line_id' => $l->id,
                        'product_id'             => $l->product_id,
                        'description'            => $l->description,
                        'quantity'               => max((float) $l->quantity - $invoiced, 0),
                        'unit'                   => $l->unit,
                        'price'                  => (float) $l->unit_price,
                        'discount_percent'       => (float) ($l->discount_percent ?? 0),
                        'tax_rate'               => (float) ($l->tax_rate ?? 0),
                    ];
                })->filter(fn ($l) => $l['quantity'] > 0)->values()->all() ?: null;
            }
        }

        return view('purchases.purchasesinvoices.create', $this->formData() + compact('prefill', 'prefillLines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $invoice = DB::transaction(fn () => $this->persist(new PurchaseInvoice(), $data));

        if ($request->boolean('validate_now')) {
            return $this->confirm($invoice);
        }

        return redirect()->route('purchases.purchasesinvoices.index')
            ->with('success', 'Purchase invoice ' . $invoice->invoice_number . ' created.');
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load(['supplier', 'purchaseOrder', 'purchaseReceipt', 'warehouse', 'items.product', 'createdBy', 'validatedBy']);

        return view('purchases.purchasesinvoices.show', compact('purchaseInvoice'));
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'draft') {
            return redirect()->route('purchases.purchasesinvoices.show', $purchaseInvoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $purchaseInvoice->load('items');

        return view('purchases.purchasesinvoices.edit', $this->formData($purchaseInvoice) + compact('purchaseInvoice'));
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'draft') {
            return redirect()->route('purchases.purchasesinvoices.show', $purchaseInvoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $data = $request->validate($this->rules());

        DB::transaction(fn () => $this->persist($purchaseInvoice, $data));

        if ($request->boolean('validate_now')) {
            return $this->confirm($purchaseInvoice);
        }

        return redirect()->route('purchases.purchasesinvoices.index')
            ->with('success', 'Purchase invoice ' . $purchaseInvoice->invoice_number . ' updated.');
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        if (!in_array($purchaseInvoice->status, ['draft', 'cancelled'], true)) {
            return back()->with('error', 'Only draft or cancelled invoices can be deleted.');
        }

        DB::transaction(function () use ($purchaseInvoice) {
            $purchaseInvoice->items()->delete();
            $purchaseInvoice->delete();
        });

        return redirect()->route('purchases.purchasesinvoices.index')
            ->with('success', 'Purchase invoice deleted.');
    }

    /**
     * Validate a draft invoice. Named "confirm" because Controller already has a validate() method.
     */
    public function confirm(PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be validated.');
        }

        if ($purchaseInvoice->affectsStock() && !$purchaseInvoice->warehouse_id) {
            return back()->with('error', 'A warehouse is required for a direct invoice (no order or receipt linked).');
        }

        DB::transaction(function () use ($purchaseInvoice) {
            $purchaseInvoice->update([
                'status'       => 'validated',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            if ($purchaseInvoice->affectsStock()) {
                $this->postStockMovements($purchaseInvoice);
            }
        });

        return redirect()->route('purchases.purchasesinvoices.show', $purchaseInvoice)
            ->with('success', 'Purchase invoice validated.');
    }

    public function cancel(PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be cancelled.');
        }

        $purchaseInvoice->update(['status' => 'cancelled']);

        return redirect()->route('purchases.purchasesinvoices.show', $purchaseInvoice)
            ->with('success', 'Purchase invoice cancelled.');
    }

    public function payment(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'validated') {
            return back()->with('error', 'Payments can only be recorded on validated invoices.');
        }

        $balance = $purchaseInvoice->balance_due;

        if ($balance <= 0) {
            return back()->with('error', 'This invoice is already fully paid.');
        }

        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:' . $balance],
            'payment_method' => ['required', Rule::in(array_keys(PurchaseInvoice::PAYMENT_METHODS))],
            'paid_at'        => ['required', 'date'],
        ]);

        $paid = round((float) $purchaseInvoice->amount_paid + (float) $data['amount'], 2);

        $purchaseInvoice->update([
            'amount_paid'    => $paid,
            'payment_method' => $data['payment_method'],
            'paid_at'        => $data['paid_at'],
            'payment_status' => $paid >= round((float) $purchaseInvoice->total, 2) ? 'paid' : 'partial',
        ]);

        return redirect()->route('purchases.purchasesinvoices.show', $purchaseInvoice)
            ->with('success', 'Payment recorded.');
    }

    // ------------------------------------------------------------------ helpers

    private function formData(?PurchaseInvoice $current = null): array
    {
        return [
            'suppliers'        => Supplier::orderBy('name')->get(),
            'warehouses'       => Warehouse::orderBy('name')->get(),
            'products'         => Product::orderBy('name')->get(),
            'purchaseOrders'   => PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received', 'received', 'closed'])
                ->when($current && $current->purchase_order_id, fn ($q) => $q->orWhere('id', $current->purchase_order_id))
                ->orderByDesc('id')
                ->get(),
            'purchaseReceipts' => PurchaseReceipt::where('status', 'validated')
                ->when($current && $current->purchase_receipt_id, fn ($q) => $q->orWhere('id', $current->purchase_receipt_id))
                ->orderByDesc('id')
                ->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'purchase_order_id'       => ['nullable', 'exists:purchase_orders,id'],
            'purchase_receipt_id'     => ['nullable', 'exists:purchase_receipts,id'],
            'supplier_id'             => ['required', 'exists:suppliers,id'],
            'warehouse_id'            => ['nullable', 'required_without_all:purchase_order_id,purchase_receipt_id', 'exists:warehouses,id'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:100'],
            'date'                    => ['required', 'date'],
            'due_date'                => ['nullable', 'date', 'after_or_equal:date'],
            'payment_terms'           => ['nullable', 'string', 'max:150'],
            'currency'                => ['required', Rule::in(['MAD', 'EUR', 'USD'])],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'           => ['nullable', 'numeric', 'min:0'],
            'notes'                   => ['nullable', 'string'],
            'internal_notes'          => ['nullable', 'string'],

            'items'                          => ['required', 'array', 'min:1'],
            'items.*.purchase_order_line_id' => ['nullable', 'integer'],
            'items.*.product_id'             => ['nullable', 'integer'],
            'items.*.description'            => ['required', 'string', 'max:255'],
            'items.*.quantity'               => ['required', 'numeric', 'min:0.001'],
            'items.*.unit'                   => ['nullable', 'string', 'max:30'],
            'items.*.price'                  => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent'       => ['nullable', 'numeric', 'between:0,100'],
            'items.*.tax_rate'               => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    private function persist(PurchaseInvoice $invoice, array $data): PurchaseInvoice
    {
        [$lines, $subtotal, $tax] = $this->buildLines($data['items']);

        $discount = (float) ($data['discount_amount'] ?? 0);
        $shipping = (float) ($data['shipping_cost'] ?? 0);
        $total    = round($subtotal - $discount + $tax + $shipping, 2);

        $invoice->fill([
            'purchase_order_id'       => $data['purchase_order_id'] ?? null,
            'purchase_receipt_id'     => $data['purchase_receipt_id'] ?? null,
            'supplier_id'             => $data['supplier_id'],
            'warehouse_id'            => $data['warehouse_id'] ?? null,
            'supplier_invoice_number' => $data['supplier_invoice_number'] ?? null,
            'date'                    => $data['date'],
            'due_date'                => $data['due_date'] ?? null,
            'payment_terms'           => $data['payment_terms'] ?? null,
            'currency'                => $data['currency'],
            'notes'                   => $data['notes'] ?? null,
            'internal_notes'          => $data['internal_notes'] ?? null,
            'subtotal'                => $subtotal,
            'discount_amount'         => $discount,
            'tax'                     => $tax,
            'shipping_cost'           => $shipping,
            'total'                   => $total,
        ]);

        if (!$invoice->exists) {
            $invoice->fill([
                'status'         => 'draft',
                'payment_status' => 'unpaid',
                'amount_paid'    => 0,
                'created_by'     => Auth::id(),
            ]);
        }

        $invoice->save();

        $invoice->items()->delete();
        $invoice->items()->createMany($lines);

        return $invoice;
    }

    private function buildLines(array $items): array
    {
        $subtotal = 0;
        $tax = 0;
        $lines = [];

        foreach ($items as $item) {
            $qty   = (float) $item['quantity'];
            $price = (float) $item['price'];
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
                'quantity'               => $qty,
                'price'                  => $price,
                'discount_percent'       => $disc,
                'tax_rate'               => $rate,
                'total'                  => $total,
            ];
        }

        return [$lines, round($subtotal, 2), round($tax, 2)];
    }

    /**
     * Direct invoices only (no purchase order / receipt): goods come in with the invoice,
     * so create one StockMovement + updateStock() per product line.
     */
    private function postStockMovements(PurchaseInvoice $invoice): void
    {
        $invoice->loadMissing('items');

        foreach ($invoice->items as $line) {
            $qty = (float) $line->quantity;

            if (!$line->product_id || $qty <= 0) {
                continue;
            }

            StockMovement::create([
                'product_id'   => $line->product_id,
                'warehouse_id' => $invoice->warehouse_id,
                'type'         => 'in',
                'quantity'     => $qty,
                'reason'       => 'purchase_invoice',
                'date'         => now(),
                'source_type'  => PurchaseInvoice::class,
                'source_id'    => $invoice->id,
            ]);

            $this->updateStock($line->product_id, $invoice->warehouse_id, $qty, 'in');
        }
    }
}
