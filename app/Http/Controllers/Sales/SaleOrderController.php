<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Product;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleOrderController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  CRUD                                                              */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $query = SaleOrder::with('customer')->latest('id');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $saleOrders = $query->paginate(15)->withQueryString();

        return view('sales.salesorders.index', compact('saleOrders'));
    }

    public function create()
    {
        return view('sales.salesorders.create', $this->formData());
    }

    /** Pre-fill the create form from an accepted quotation. */
    public function createFromQuotation(SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'accepted') {
            return redirect()->route('sales.salesquotations.show', $saleQuotation)
                ->with('error', 'Only accepted quotations can be converted into a sales order.');
        }

        $existing = SaleOrder::where('quotation_id', $saleQuotation->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existing) {
            return redirect()->route('sales.salesorders.show', $existing)
                ->with('error', 'This quotation has already been converted into order ' . $existing->order_number . '.');
        }

        $saleQuotation->load('items');

        $prefill = [
            'quotation_id' => $saleQuotation->id,
            'customer_id' => $saleQuotation->customer_id,
            'reference' => $saleQuotation->reference,
            'currency' => $saleQuotation->currency,
            'payment_terms' => $saleQuotation->payment_terms,
            'delivery_terms' => $saleQuotation->delivery_terms,
            'notes' => $saleQuotation->notes,
            'terms_conditions' => $saleQuotation->terms_conditions,
            'discount_amount' => $saleQuotation->discount_amount,
            'shipping_cost' => $saleQuotation->shipping_cost,
        ];

        $prefillItems = $saleQuotation->items->map(fn($l) => [
            'product_id' => $l->product_id,
            'description' => $l->description,
            'quantity' => (float) $l->quantity,
            'unit' => $l->unit,
            'price' => (float) $l->price,
            'discount_percent' => (float) $l->discount_percent,
            'tax_rate' => (float) $l->tax_rate,
        ])->values()->all();

        return view('sales.salesorders.create', $this->formData() + compact('prefill', 'prefillItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        if (!empty($data['quotation_id'])) {
            $quotation = SaleQuotation::find($data['quotation_id']);
            if (!$quotation || $quotation->status !== 'accepted') {
                return back()->withInput()->with('error', 'The linked quotation must be accepted.');
            }
        }

        $order = $this->persist(null, $data);

        if ($request->boolean('confirm_now')) {
            $this->markConfirmed($order);
        }

        return redirect()->route('sales.salesorders.show', $order)
            ->with('success', 'Sales order ' . $order->order_number . ' created.');
    }

    public function show(SaleOrder $saleOrder)
    {
        $saleOrder->load(['customer', 'quotation', 'createdBy', 'items.product']);

        return view('sales.salesorders.show', compact('saleOrder'));
    }

    public function edit(SaleOrder $saleOrder)
    {
        if ($saleOrder->status !== 'draft') {
            return redirect()->route('sales.salesorders.show', $saleOrder)
                ->with('error', 'Only draft orders can be edited.');
        }

        $saleOrder->load('items');

        return view('sales.salesorders.edit', $this->formData() + compact('saleOrder'));
    }

    public function update(Request $request, SaleOrder $saleOrder)
    {
        if ($saleOrder->status !== 'draft') {
            return redirect()->route('sales.salesorders.show', $saleOrder)
                ->with('error', 'Only draft orders can be edited.');
        }

        $data = $request->validate($this->rules());

        $this->persist($saleOrder, $data);

        if ($request->boolean('confirm_now')) {
            $this->markConfirmed($saleOrder);
        }

        return redirect()->route('sales.salesorders.show', $saleOrder)
            ->with('success', 'Sales order updated.');
    }

    public function destroy(SaleOrder $saleOrder)
    {
        if (!in_array($saleOrder->status, ['draft', 'cancelled'], true)) {
            return back()->with('error', 'Only draft or cancelled orders can be deleted.');
        }

        DB::transaction(function () use ($saleOrder) {
            $saleOrder->items()->delete();
            $saleOrder->delete();
        });

        return redirect()->route('sales.salesorders.index')
            ->with('success', 'Sales order deleted.');
    }

    /* ------------------------------------------------------------------ */
    /*  Workflow: draft → confirmed → processing → delivered              */
    /* ------------------------------------------------------------------ */

    public function confirm(SaleOrder $saleOrder)
    {
        if ($saleOrder->status !== 'draft') {
            return back()->with('error', 'Only draft orders can be confirmed.');
        }

        $this->markConfirmed($saleOrder);

        return back()->with('success', 'Order confirmed.');
    }

    public function process(SaleOrder $saleOrder)
    {
        if ($saleOrder->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed orders can be moved to processing.');
        }

        $saleOrder->update(['status' => 'processing']);

        return back()->with('success', 'Order is now being processed.');
    }

    public function deliver(SaleOrder $saleOrder)
    {
        if (!in_array($saleOrder->status, ['confirmed', 'processing'], true)) {
            return back()->with('error', 'Only confirmed or processing orders can be marked as delivered.');
        }

        DB::transaction(function () use ($saleOrder) {
            $saleOrder->items()->update(['delivered_quantity' => DB::raw('quantity')]);
            $saleOrder->update(['status' => 'delivered', 'delivered_at' => now()]);
        });

        return back()->with('success', 'Order marked as delivered.');
    }

    public function cancel(Request $request, SaleOrder $saleOrder)
    {
        if (!in_array($saleOrder->status, ['draft', 'confirmed', 'processing'], true)) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $data = $request->validate(['cancellation_reason' => ['nullable', 'string', 'max:255']]);

        $saleOrder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $data['cancellation_reason'] ?? null,
        ]);

        return back()->with('success', 'Order cancelled.');
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                         */
    /* ------------------------------------------------------------------ */

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'customer_id' => ['required', Rule::exists((new Customer)->getTable(), 'id')],
            'quotation_id' => ['nullable', Rule::exists((new SaleQuotation)->getTable(), 'id')],
            'reference' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', Rule::in(['MAD', 'EUR', 'USD'])],
            'date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:date'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'delivery_terms' => ['nullable', 'string', 'max:255'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', Rule::exists((new Product)->getTable(), 'id')],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /** Totals are always recomputed server-side; the browser values are never trusted. */
    private function computeTotals(array $items, float $discount, float $shipping): array
    {
        $subtotal = 0.0;
        $tax = 0.0;
        $lines = [];

        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['price'];
            $disc = (float) ($item['discount_percent'] ?? 0);
            $rate = (float) ($item['tax_rate'] ?? 0);

            $lineTotal = round($qty * $price * (1 - $disc / 100), 2);

            $subtotal += $lineTotal;
            $tax += $lineTotal * $rate / 100;

            $lines[] = [
                'product_id' => ($item['product_id'] ?? null) ?: null,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit' => ($item['unit'] ?? null) ?: null,
                'price' => $price,
                'discount_percent' => $disc,
                'tax_rate' => $rate,
                'total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $tax = round($tax, 2);

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => round($subtotal - $discount + $tax + $shipping, 2),
        ];
    }

    private function persist(?SaleOrder $order, array $data): SaleOrder
    {
        $discount = (float) ($data['discount_amount'] ?? 0);
        $shipping = (float) ($data['shipping_cost'] ?? 0);
        $totals = $this->computeTotals($data['items'], $discount, $shipping);

        $header = array_merge(
            Arr::only($data, [
                'customer_id', 'quotation_id', 'reference', 'currency', 'date',
                'expected_delivery_date', 'payment_terms', 'delivery_terms',
                'shipping_address', 'billing_address',
                'notes', 'terms_conditions', 'internal_notes',
            ]),
            [
                'discount_amount' => $discount,
                'shipping_cost' => $shipping,
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
            ]
        );

        return DB::transaction(function () use ($order, $header, $totals) {
            if ($order) {
                unset($header['quotation_id']); // the origin quotation never changes
                $order->update($header);
                $order->items()->delete();
            } else {
                $order = SaleOrder::create($header + [
                    'order_number' => SaleOrder::generateNumber(),
                    'status' => 'draft',
                    'created_by' => Auth::id(),
                ]);
            }

            $order->items()->createMany($totals['lines']);

            return $order;
        });
    }

    private function markConfirmed(SaleOrder $order): void
    {
        $order->update(['status' => 'confirmed', 'confirmed_at' => now()]);
    }
}
