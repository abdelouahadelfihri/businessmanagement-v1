<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Warehouse;
use App\Models\Sales\SaleDelivery;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderLine;
use App\Services\Sales\SaleDeliveryStockService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleDeliveryController extends Controller
{
    private const DELIVERABLE_ORDER_STATUSES = ['confirmed', 'processing'];

    public function __construct(private SaleDeliveryStockService $stock)
    {
    }

    /* ------------------------------------------------------------------ */
    /*  CRUD                                                              */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $query = SaleDelivery::with(['salesOrder.customer', 'warehouse'])->latest('id');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('salesOrder', function ($o) use ($search) {
                        $o->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($warehouse = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouse);
        }

        $saleDeliveries = $query->paginate(15)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('sales.salesdeliveries.index', compact('saleDeliveries', 'warehouses'));
    }

    /**
     * Step 1: no order chosen yet → order picker.
     * Step 2: ?sales_order_id=X → full form pre-filled with the quantities still to deliver.
     */
    public function create(Request $request)
    {
        $orders = SaleOrder::with('customer')
            ->whereIn('status', self::DELIVERABLE_ORDER_STATUSES)
            ->orderByDesc('id')
            ->get();

        $saleOrder = null;
        $lines = [];
        $prefill = [];

        if ($orderId = $request->input('sales_order_id')) {
            $saleOrder = SaleOrder::with(['customer', 'items.product'])->findOrFail($orderId);

            if (!in_array($saleOrder->status, self::DELIVERABLE_ORDER_STATUSES, true)) {
                return redirect()->route('sales.salesorders.show', $saleOrder)
                    ->with('error', 'Only confirmed or processing orders can be delivered.');
            }

            $lines = $this->buildLines($saleOrder, null);

            if (empty($lines)) {
                return redirect()->route('sales.salesorders.show', $saleOrder)
                    ->with('error', 'Everything on this order has already been delivered.');
            }

            $prefill = [
                'shipping_address' => $saleOrder->shipping_address,
                'recipient_name' => optional($saleOrder->customer)->name,
            ];
        }

        return view('sales.salesdeliveries.create', [
            'orders' => $orders,
            'saleOrder' => $saleOrder,
            'lines' => $lines,
            'prefill' => $prefill,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $order = SaleOrder::with('items')->findOrFail($data['sales_order_id']);

        if (!in_array($order->status, self::DELIVERABLE_ORDER_STATUSES, true)) {
            return back()->withInput()->with('error', 'Only confirmed or processing orders can be delivered.');
        }

        $delivery = $this->persist(null, $data, $order);

        if ($request->boolean('validate_now')) {
            try {
                $this->validateDelivery($delivery);
            } catch (DomainException $e) {
                return redirect()->route('sales.salesdeliveries.show', $delivery)
                    ->with('error', 'Saved as draft, but not validated: ' . $e->getMessage());
            }

            return redirect()->route('sales.salesdeliveries.show', $delivery)
                ->with('success', 'Delivery ' . $delivery->delivery_number . ' validated — stock updated.');
        }

        return redirect()->route('sales.salesdeliveries.show', $delivery)
            ->with('success', 'Delivery ' . $delivery->delivery_number . ' saved as draft.');
    }

    public function show(SaleDelivery $saleDelivery)
    {
        $saleDelivery->load(['salesOrder.customer', 'warehouse', 'lines.product', 'stockMovements', 'createdBy']);

        return view('sales.salesdeliveries.show', compact('saleDelivery'));
    }

    public function edit(SaleDelivery $saleDelivery)
    {
        if ($saleDelivery->status !== 'draft') {
            return redirect()->route('sales.salesdeliveries.show', $saleDelivery)
                ->with('error', 'Only draft deliveries can be edited.');
        }

        $saleDelivery->load('lines');
        $saleOrder = SaleOrder::with(['customer', 'items.product'])->findOrFail($saleDelivery->sales_order_id);

        return view('sales.salesdeliveries.edit', [
            'saleDelivery' => $saleDelivery,
            'saleOrder' => $saleOrder,
            'lines' => $this->buildLines($saleOrder, $saleDelivery),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SaleDelivery $saleDelivery)
    {
        if ($saleDelivery->status !== 'draft') {
            return redirect()->route('sales.salesdeliveries.show', $saleDelivery)
                ->with('error', 'Only draft deliveries can be edited.');
        }

        $data = $request->validate($this->rules());

        // The order of a delivery never changes
        $order = SaleOrder::with('items')->findOrFail($saleDelivery->sales_order_id);
        $data['sales_order_id'] = $order->id;

        $this->persist($saleDelivery, $data, $order);

        if ($request->boolean('validate_now')) {
            try {
                $this->validateDelivery($saleDelivery);
            } catch (DomainException $e) {
                return redirect()->route('sales.salesdeliveries.show', $saleDelivery)
                    ->with('error', 'Saved as draft, but not validated: ' . $e->getMessage());
            }

            return redirect()->route('sales.salesdeliveries.show', $saleDelivery)
                ->with('success', 'Delivery validated — stock updated.');
        }

        return redirect()->route('sales.salesdeliveries.show', $saleDelivery)
            ->with('success', 'Delivery updated.');
    }

    public function destroy(SaleDelivery $saleDelivery)
    {
        $deletable = $saleDelivery->status === 'draft'
            || ($saleDelivery->status === 'cancelled' && $saleDelivery->stockMovements()->doesntExist());

        if (!$deletable) {
            return back()->with('error', 'Only draft deliveries can be deleted (a delivery that moved stock is kept for the audit trail).');
        }

        DB::transaction(function () use ($saleDelivery) {
            $saleDelivery->lines()->delete();
            $saleDelivery->delete();
        });

        return redirect()->route('sales.salesdeliveries.index')
            ->with('success', 'Delivery deleted.');
    }

    /* ------------------------------------------------------------------ */
    /*  Workflow: draft → validated (stock out) → cancelled (stock back)  */
    /* ------------------------------------------------------------------ */

    public function confirm(SaleDelivery $saleDelivery)
    {
        try {
            $this->validateDelivery($saleDelivery);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Delivery validated — stock updated.');
    }

    public function cancel(Request $request, SaleDelivery $saleDelivery)
    {
        $data = $request->validate(['cancellation_reason' => ['nullable', 'string', 'max:255']]);

        try {
            DB::transaction(function () use ($saleDelivery, $data) {
                $delivery = SaleDelivery::whereKey($saleDelivery->id)->lockForUpdate()->firstOrFail();

                if (!in_array($delivery->status, ['draft', 'validated'], true)) {
                    throw new DomainException('This delivery can no longer be cancelled.');
                }

                $wasValidated = $delivery->status === 'validated';

                if ($wasValidated) {
                    $order = SaleOrder::whereKey($delivery->sales_order_id)->lockForUpdate()->firstOrFail();

                    if ($order->status === 'invoiced') {
                        throw new DomainException('The order is already invoiced: this delivery cannot be cancelled.');
                    }

                    $delivery->load('lines');

                    // 1. Stock comes back
                    $this->stock->reverse($delivery);

                    // 2. Order lines get their quantities back
                    foreach ($delivery->lines as $line) {
                        if ($line->order_line_id) {
                            SaleOrderLine::whereKey($line->order_line_id)
                                ->decrement('delivered_quantity', (float) $line->quantity);
                        }
                    }
                }

                $delivery->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $data['cancellation_reason'] ?? null,
                ]);

                if ($wasValidated) {
                    $this->syncOrderStatus($order->fresh('items'));
                }
            });
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Delivery cancelled.');
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * The whole "validate" operation, atomic:
     * checks → stock out → order lines updated → delivery validated → order status synced.
     * Throws DomainException (and rolls everything back) on any problem.
     */
    private function validateDelivery(SaleDelivery $saleDelivery): void
    {
        DB::transaction(function () use ($saleDelivery) {
            $delivery = SaleDelivery::whereKey($saleDelivery->id)->lockForUpdate()->firstOrFail();

            if ($delivery->status !== 'draft') {
                throw new DomainException('Only draft deliveries can be validated.');
            }
            if (!$delivery->warehouse_id) {
                throw new DomainException('Choose a warehouse before validating.');
            }

            $order = SaleOrder::whereKey($delivery->sales_order_id)->lockForUpdate()->with('items')->firstOrFail();

            if (!in_array($order->status, self::DELIVERABLE_ORDER_STATUSES, true)) {
                throw new DomainException("Order {$order->order_number} is {$order->status}: it cannot be delivered.");
            }

            $delivery->load('lines.product');

            if ($delivery->lines->isEmpty()) {
                throw new DomainException('This delivery has no lines.');
            }

            // Re-check what is left to deliver (another delivery may have been validated meanwhile)
            $orderLines = $order->items->keyBy('id');
            foreach ($delivery->lines as $line) {
                $orderLine = $orderLines->get($line->order_line_id);
                if (!$orderLine) {
                    continue;
                }

                $remaining = (float) $orderLine->quantity - (float) $orderLine->delivered_quantity;
                if ((float) $line->quantity > $remaining + 0.0005) {
                    throw new DomainException(sprintf(
                        'Line "%s": only %s left to deliver on the order.',
                        $line->description,
                        rtrim(rtrim(number_format($remaining, 3, '.', ''), '0'), '.') ?: '0'
                    ));
                }
            }

            // Stock check + "out" movements (throws if not enough stock)
            $this->stock->issue($delivery);

            foreach ($delivery->lines as $line) {
                if ($line->order_line_id) {
                    SaleOrderLine::whereKey($line->order_line_id)
                        ->increment('delivered_quantity', (float) $line->quantity);
                }
            }

            $delivery->update(['status' => 'validated', 'validated_at' => now()]);

            $this->syncOrderStatus($order->fresh('items'));
        });
    }

    /** confirmed ↔ processing ↔ delivered, depending on what has really been delivered. */
    private function syncOrderStatus(SaleOrder $order): void
    {
        if (!in_array($order->status, ['confirmed', 'processing', 'delivered'], true)) {
            return;
        }

        $items = $order->items;
        $anyDelivered = $items->sum(fn($l) => (float) $l->delivered_quantity) > 0;
        $allDelivered = $items->isNotEmpty()
            && $items->every(fn($l) => (float) $l->delivered_quantity + 0.0005 >= (float) $l->quantity);

        if ($allDelivered) {
            $order->update(['status' => 'delivered', 'delivered_at' => $order->delivered_at ?? now()]);
        } elseif ($anyDelivered) {
            $order->update(['status' => 'processing', 'delivered_at' => null]);
        } else {
            $order->update(['status' => 'confirmed', 'delivered_at' => null]);
        }
    }

    /** One row per order line, with ordered / already delivered / remaining quantities. */
    private function buildLines(SaleOrder $order, ?SaleDelivery $delivery): array
    {
        $existing = $delivery ? $delivery->lines->keyBy('order_line_id') : collect();

        return $order->items
            ->map(function ($l) use ($existing, $delivery) {
                $remaining = max(0.0, (float) $l->quantity - (float) $l->delivered_quantity);

                return [
                    'order_line_id' => $l->id,
                    'product_id' => $l->product_id,
                    'product' => optional($l->product)->name,
                    'description' => $l->description,
                    'unit' => $l->unit,
                    'ordered' => (float) $l->quantity,
                    'delivered' => (float) $l->delivered_quantity,
                    'remaining' => $remaining,
                    'price' => (float) $l->price,
                    'discount_percent' => (float) $l->discount_percent,
                    'tax_rate' => (float) $l->tax_rate,
                    'quantity' => $delivery
                        ? (float) optional($existing->get($l->id))->quantity
                        : $remaining,
                ];
            })
            // New delivery: only lines with something left. Edit: keep every line.
            ->filter(fn($row) => $delivery ? true : $row['remaining'] > 0)
            ->values()
            ->all();
    }

    private function rules(): array
    {
        return [
            'sales_order_id' => ['required', Rule::exists((new SaleOrder)->getTable(), 'id')],
            'warehouse_id' => ['required', Rule::exists((new Warehouse)->getTable(), 'id')],
            'date' => ['required', 'date'],
            'carrier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.order_line_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Prices, descriptions, units and tax rates come from the ORDER lines (never from the browser);
     * the form only decides how much of each line is delivered.
     */
    private function persist(?SaleDelivery $delivery, array $data, SaleOrder $order): SaleDelivery
    {
        $orderLines = $order->items->keyBy('id');

        $lines = [];
        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($data['items'] as $i => $item) {
            $qty = (float) $item['quantity'];
            if ($qty <= 0) {
                continue;
            }

            $orderLine = $orderLines->get((int) $item['order_line_id']);
            if (!$orderLine) {
                throw ValidationException::withMessages(["items.$i.order_line_id" => 'This line does not belong to the selected order.']);
            }

            $remaining = (float) $orderLine->quantity - (float) $orderLine->delivered_quantity;
            if ($qty > $remaining + 0.0005) {
                throw ValidationException::withMessages([
                    "items.$i.quantity" => "\"{$orderLine->description}\": only " . rtrim(rtrim(number_format($remaining, 3, '.', ''), '0'), '.') . ' left to deliver.',
                ]);
            }

            $price = (float) $orderLine->price;
            $disc = (float) $orderLine->discount_percent;
            $rate = (float) $orderLine->tax_rate;
            $lineTotal = round($qty * $price * (1 - $disc / 100), 2);

            $subtotal += $lineTotal;
            $tax += $lineTotal * $rate / 100;

            $lines[] = [
                'order_line_id' => $orderLine->id,
                'product_id' => $orderLine->product_id,
                'description' => $orderLine->description,
                'quantity' => $qty,
                'unit' => $orderLine->unit,
                'unit_price' => $price,
                'discount_percent' => $disc,
                'tax_rate' => $rate,
                'total' => $lineTotal,
            ];
        }

        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Enter a quantity greater than zero on at least one line.']);
        }

        $subtotal = round($subtotal, 2);
        $tax = round($tax, 2);

        $header = array_merge(
            Arr::only($data, [
                'sales_order_id', 'warehouse_id', 'date', 'carrier', 'tracking_number',
                'shipping_address', 'recipient_name', 'recipient_phone', 'notes', 'internal_notes',
            ]),
            [
                'currency' => $order->currency,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => round($subtotal + $tax, 2),
            ]
        );

        return DB::transaction(function () use ($delivery, $header, $lines) {
            if ($delivery) {
                unset($header['sales_order_id']);
                $delivery->update($header);
                $delivery->lines()->delete();
            } else {
                $delivery = SaleDelivery::create($header + [
                    'delivery_number' => SaleDelivery::generateNumber(),
                    'status' => 'draft',
                    'created_by' => Auth::id(),
                ]);
            }

            $delivery->lines()->createMany($lines);

            return $delivery;
        });
    }
}
