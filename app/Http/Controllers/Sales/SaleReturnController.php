<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Customer;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Warehouse;
use App\Models\Sales\SaleDelivery;
use App\Models\Sales\SaleInvoice;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleReturn;
use App\Models\Sales\SaleReturnLine;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SaleReturnController extends Controller
{
    /**
     * Values written in the stock movement "type" column.
     * Change them if your StockMovement uses other values (e.g. 'IN' / 'OUT').
     */
    private const STOCK_IN = 'in';
    private const STOCK_OUT = 'out';

    public function index(Request $request)
    {
        $query = SaleReturn::with(['customer', 'salesOrder', 'warehouse'])
            ->orderByDesc('date')->orderByDesc('id');

        if ($s = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($s) {
                $q->where('return_number', 'like', "%{$s}%")
                    ->orWhere('reference', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('salesOrder', fn($o) => $o->where('order_number', 'like', "%{$s}%"));
            });
        }

        foreach (['warehouse_id', 'customer_id', 'status', 'reason_type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $saleReturns = $query->paginate(15)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return view('sales.salesreturns.index', compact('saleReturns', 'warehouses', 'customers'));
    }

    public function create(Request $request)
    {
        $saleOrder = null;
        $lines = [];
        $prefill = [];
        $invoices = collect();

        if ($request->filled('sales_order_id')) {
            $saleOrder = SaleOrder::with(['customer', 'lines.product'])->findOrFail($request->sales_order_id);
            $lines = $this->buildLines($saleOrder);

            if (collect($lines)->sum('returnable') <= 0) {
                return redirect()->route('sales.salesreturns.create')
                    ->with('error', 'Nothing left to return on this order (no delivered quantity remaining).');
            }

            $prefill = [
                'currency' => $saleOrder->currency,
                // by default the goods come back where they were shipped from
                'warehouse_id' => SaleDelivery::where('sales_order_id', $saleOrder->id)
                    ->where('status', 'validated')->latest('id')->value('warehouse_id'),
            ];
            $invoices = $this->invoicesFor($saleOrder);
        }

        $orders = $saleOrder ? collect() : $this->returnableOrders();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('sales.salesreturns.create', compact(
            'saleOrder', 'orders', 'lines', 'prefill', 'invoices', 'warehouses'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $order = SaleOrder::with('lines')->findOrFail($data['sales_order_id']);

        $return = DB::transaction(function () use ($data, $order, $request) {
            $return = new SaleReturn();
            $return->status = 'draft';
            $return->created_by = Auth::id();

            $this->persist($return, $order, $data, $request->input('items', []));

            if ($request->boolean('validate_now')) {
                $this->doValidate($return);
            }

            return $return;
        });

        return redirect()->route('sales.salesreturns.show', $return)
            ->with('success', $return->status === 'validated'
                ? 'Return validated: stock updated.'
                : 'Return saved as draft.');
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['customer', 'salesOrder', 'warehouse', 'invoice', 'lines.product', 'stockMovements', 'createdBy']);

        return view('sales.salesreturns.show', compact('saleReturn'));
    }

    public function edit(SaleReturn $saleReturn)
    {
        if (!$saleReturn->isDraft()) {
            return redirect()->route('sales.salesreturns.show', $saleReturn)
                ->with('error', 'Only draft returns can be edited.');
        }

        $saleOrder = SaleOrder::with(['customer', 'lines.product'])->findOrFail($saleReturn->sales_order_id);
        $lines = $this->buildLines($saleOrder, $saleReturn);
        $prefill = [];
        $invoices = $this->invoicesFor($saleOrder);
        $warehouses = Warehouse::orderBy('name')->get();

        return view('sales.salesreturns.edit', compact(
            'saleReturn', 'saleOrder', 'lines', 'prefill', 'invoices', 'warehouses'
        ));
    }

    public function update(Request $request, SaleReturn $saleReturn)
    {
        if (!$saleReturn->isDraft()) {
            return redirect()->route('sales.salesreturns.show', $saleReturn)
                ->with('error', 'Only draft returns can be edited.');
        }

        $data = $this->validated($request);
        $data['sales_order_id'] = $saleReturn->sales_order_id; // the order never changes
        $order = SaleOrder::with('lines')->findOrFail($saleReturn->sales_order_id);

        DB::transaction(function () use ($saleReturn, $order, $data, $request) {
            $this->persist($saleReturn, $order, $data, $request->input('items', []));

            if ($request->boolean('validate_now')) {
                $this->doValidate($saleReturn);
            }
        });

        return redirect()->route('sales.salesreturns.show', $saleReturn)
            ->with('success', $saleReturn->status === 'validated'
                ? 'Return validated: stock updated.'
                : 'Return updated.');
    }

    public function destroy(SaleReturn $saleReturn)
    {
        if (!$saleReturn->isDraft()) {
            return redirect()->route('sales.salesreturns.index')
                ->with('error', 'Only draft returns can be deleted. Cancel the return instead.');
        }

        DB::transaction(function () use ($saleReturn) {
            $saleReturn->lines()->delete();
            $saleReturn->delete();
        });

        return redirect()->route('sales.salesreturns.index')->with('success', 'Draft return deleted.');
    }

    /** Draft -> Validated (goods go back into the stock). Not named validate(): the base Controller has one. */
    public function validateReturn(SaleReturn $saleReturn)
    {
        if (!$saleReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be validated.');
        }

        if ($saleReturn->lines()->count() === 0) {
            return back()->with('error', 'This return has no lines.');
        }

        DB::transaction(fn() => $this->doValidate($saleReturn));

        return redirect()->route('sales.salesreturns.show', $saleReturn)
            ->with('success', 'Return validated: stock updated.');
    }

    public function cancel(Request $request, SaleReturn $saleReturn)
    {
        if (!in_array($saleReturn->status, ['draft', 'validated'], true)) {
            return back()->with('error', 'This return cannot be cancelled.');
        }

        $request->validate(['cancellation_reason' => 'nullable|string|max:255']);

        DB::transaction(function () use ($saleReturn, $request) {
            // A validated return already put goods back in stock: take them out again.
            if ($saleReturn->status === 'validated') {
                $saleReturn->load('lines');
                $this->recordStockMovements($saleReturn, reverse: true);
            }

            $saleReturn->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->input('cancellation_reason'),
            ]);
        });

        return redirect()->route('sales.salesreturns.show', $saleReturn)->with('success', 'Return cancelled.');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    private function validated(Request $request): array
    {
        return $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'reason_type' => 'required|in:' . implode(',', array_keys(SaleReturn::REASONS)),
            'reason' => 'nullable|string|max:1000',
            'refund_method' => 'nullable|string|max:50',
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.order_line_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.condition' => 'nullable|in:' . implode(',', array_keys(SaleReturn::CONDITIONS)),
            'items.*.restock' => 'nullable|boolean',
        ]);
    }

    /** Orders that have at least one validated delivery. */
    private function returnableOrders()
    {
        $ids = SaleDelivery::where('status', 'validated')->pluck('sales_order_id')->unique();

        return SaleOrder::with('customer')->whereIn('id', $ids)->orderByDesc('id')->get();
    }

    private function invoicesFor(SaleOrder $order)
    {
        return SaleInvoice::where('sales_order_id', $order->id)
            ->whereIn('status', ['issued', 'partial', 'paid'])
            ->orderByDesc('id')
            ->get(['id', 'invoice_number']);
    }

    /** [order_line_id => delivered quantity] from VALIDATED deliveries of this order. */
    private function deliveredByOrderLine(SaleOrder $order): array
    {
        $deliveries = SaleDelivery::with('lines')
            ->where('sales_order_id', $order->id)
            ->where('status', 'validated')
            ->get();

        $map = [];
        foreach ($deliveries as $delivery) {
            foreach ($delivery->lines as $line) {
                $key = $line->order_line_id;
                $map[$key] = ($map[$key] ?? 0) + (float) $line->quantity;
            }
        }

        return $map;
    }

    /** Quantity of an order line already returned on non-cancelled returns. */
    private function returnedQty(int $orderLineId, ?int $excludeReturnId = null): float
    {
        return (float) SaleReturnLine::where('order_line_id', $orderLineId)
            ->whereHas('salesReturn', function ($q) use ($excludeReturnId) {
                $q->where('status', '!=', 'cancelled');
                if ($excludeReturnId) {
                    $q->where('id', '!=', $excludeReturnId);
                }
            })
            ->sum('quantity');
    }

    /** Rows shown in the form: delivered / already returned / returnable per delivered order line. */
    private function buildLines(SaleOrder $order, ?SaleReturn $return = null): array
    {
        $delivered = $this->deliveredByOrderLine($order);
        $current = $return ? $return->lines()->get()->keyBy('order_line_id') : collect();

        $rows = [];

        foreach ($order->lines as $ol) {
            $deliveredQty = (float) ($delivered[$ol->id] ?? 0);
            if ($deliveredQty <= 0) {
                continue; // never delivered, nothing to return
            }

            $returned = $this->returnedQty($ol->id, $return?->id);
            $existing = $current->get($ol->id);

            $rows[] = [
                'order_line_id' => $ol->id,
                'product' => $ol->product->name ?? null,
                'description' => $ol->description ?? ($ol->product->name ?? null),
                'unit' => $ol->unit ?? null,
                'delivered' => $deliveredQty,
                'returned' => $returned,
                'returnable' => max(0, $deliveredQty - $returned),
                'quantity' => $existing ? (float) $existing->quantity : 0,
                'price' => (float) $ol->price,
                'discount_percent' => (float) ($ol->discount_percent ?? 0),
                'tax_rate' => (float) ($ol->tax_rate ?? 0),
                'condition' => $existing->condition ?? 'good',
                'restock' => $existing ? (bool) $existing->restock : true,
            ];
        }

        return $rows;
    }

    /**
     * Recomputes everything server-side from the order lines (prices are never
     * taken from the browser), then saves the return header and its lines.
     */
    private function persist(SaleReturn $return, SaleOrder $order, array $data, array $items): void
    {
        $orderLines = $order->lines->keyBy('id');
        $delivered = $this->deliveredByOrderLine($order);

        $computed = [];
        $subtotal = $tax = $discountTotal = 0.0;

        foreach ($items as $i => $item) {
            $qty = round((float) ($item['quantity'] ?? 0), 3);
            if ($qty <= 0) {
                continue;
            }

            $ol = $orderLines->get((int) ($item['order_line_id'] ?? 0));
            if (!$ol) {
                throw ValidationException::withMessages(["items.$i.order_line_id" => 'Invalid order line.']);
            }

            $returnable = (float) ($delivered[$ol->id] ?? 0)
                - $this->returnedQty($ol->id, $return->exists ? $return->id : null);

            if ($qty > $returnable + 0.0005) {
                throw ValidationException::withMessages([
                    "items.$i.quantity" => 'Quantity exceeds what can still be returned ('
                        . rtrim(rtrim(number_format(max(0, $returnable), 3, '.', ''), '0'), '.') . ').',
                ]);
            }

            $price = (float) $ol->price;
            $disc = (float) ($ol->discount_percent ?? 0);
            $rate = (float) ($ol->tax_rate ?? 0);

            $gross = $qty * $price;
            $lineDisc = $gross * $disc / 100;
            $lineNet = $gross - $lineDisc;
            $lineTax = $lineNet * $rate / 100;

            $subtotal += $lineNet;
            $tax += $lineTax;
            $discountTotal += $lineDisc;

            $computed[] = [
                'order_line_id' => $ol->id,
                'product_id' => $ol->product_id ?? null,
                'description' => $ol->description ?? ($ol->product->name ?? null),
                'unit' => $ol->unit ?? null,
                'quantity' => $qty,
                'unit_price' => $price,
                'discount_percent' => $disc,
                'tax_rate' => $rate,
                'tax_amount' => round($lineTax, 2),
                'subtotal' => round($lineNet, 2),
                'total' => round($lineNet + $lineTax, 2),
                'condition' => $item['condition'] ?? 'good',
                'restock' => (bool) ($item['restock'] ?? false),
                'sort_order' => count($computed),
            ];
        }

        if (empty($computed)) {
            throw ValidationException::withMessages([
                'items' => 'Enter a quantity greater than zero on at least one line.',
            ]);
        }

        $return->fill([
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'invoice_id' => $data['invoice_id'] ?? null,
            'warehouse_id' => $data['warehouse_id'],
            'currency' => $order->currency ?? 'MAD',
            'date' => $data['date'],
            'reference' => $data['reference'] ?? null,
            'reason_type' => $data['reason_type'],
            'reason' => $data['reason'] ?? null,
            'refund_method' => $data['refund_method'] ?? null,
            'notes' => $data['notes'] ?? null,
            'internal_notes' => $data['internal_notes'] ?? null,
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'tax' => round($tax, 2),
            'total' => round($subtotal + $tax, 2),
        ]);

        if (!$return->return_number) {
            $return->return_number = SaleReturn::generateNumber();
        }

        $return->save();

        $return->lines()->delete();
        foreach ($computed as $row) {
            $return->lines()->create($row);
        }
    }

    /** Must run inside a DB transaction. */
    private function doValidate(SaleReturn $return): void
    {
        $return->load('lines');

        $this->recordStockMovements($return);

        $return->status = 'validated';
        $return->validated_at = now();
        $return->save();
    }

    /**
     * Writes one stock movement per restocked line:
     *  - validation  => IN  (the customer's goods come back into the warehouse)
     *  - cancellation => OUT (reversal, history is kept)
     *
     * Only the columns that really exist in your stock_movements table are filled,
     * so you can adapt the candidate attributes below without breaking anything.
     */
    private function recordStockMovements(SaleReturn $return, bool $reverse = false): void
    {
        $columns = Schema::getColumnListing((new StockMovement())->getTable());
        $type = $reverse ? self::STOCK_OUT : self::STOCK_IN;
        $label = ($reverse ? 'Cancellation of sales return ' : 'Sales return ') . $return->return_number;

        foreach ($return->lines as $line) {
            // damaged / unsellable goods are not put back in the sellable stock
            if (!$line->restock || (float) $line->quantity <= 0 || !$line->product_id) {
                continue;
            }

            $attributes = [
                'product_id' => $line->product_id,
                'warehouse_id' => $return->warehouse_id,
                'type' => $type,
                'movement_type' => $type,
                'quantity' => $line->quantity,
                'date' => $return->date,
                'movement_date' => $return->date,
                'reference' => $return->return_number,
                'description' => $label,
                'notes' => $label,
                'note' => $label,
                'created_by' => Auth::id(),
                'user_id' => Auth::id(),
            ];

            $movement = new StockMovement();
            $movement->forceFill(Arr::only($attributes, $columns));
            $return->stockMovements()->save($movement); // sets source_type / source_id

            $this->applyStockBalance(
                (int) $line->product_id,
                (int) $return->warehouse_id,
                $reverse ? -(float) $line->quantity : (float) $line->quantity
            );
        }
    }

    /**
     * Hook for apps that keep a stock balance table (e.g. `stocks`, `product_warehouse`)
     * in addition to the movements journal. Mirror what your delivery validation does.
     * Does nothing by default (stock = sum of movements).
     */
    private function applyStockBalance(int $productId, int $warehouseId, float $delta): void
    {
        // Example:
        // \App\Models\MasterData\Stock::updateOrCreate(
        //     ['product_id' => $productId, 'warehouse_id' => $warehouseId],
        //     []
        // )->increment('quantity', $delta);
    }
}
