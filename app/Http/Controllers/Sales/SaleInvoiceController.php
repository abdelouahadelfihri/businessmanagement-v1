<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Customer;
use App\Models\Sales\SaleInvoice;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SalesInvoiceLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Type\Decimal;

class SaleInvoiceController extends Controller
{
    /** Order statuses that can be invoiced. Adjust to your SaleOrder statuses. */
    private const INVOICABLE_ORDER_STATUSES = ['confirmed', 'processing', 'shipped', 'delivered', 'completed'];

    public function index(Request $request)
    {
        $query = SaleInvoice::with(['customer', 'salesOrder'])->orderByDesc('date')->orderByDesc('id');

        if ($s = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhere('reference', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('salesOrder', fn($o) => $o->where('order_number', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($status = $request->input('status')) {
            if ($status === 'overdue') {
                $query->whereIn('status', ['issued', 'partial'])->whereDate('due_date', '<', today());
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $saleInvoices = $query->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name')->get();

        return view('sales.salesinvoices.index', compact('saleInvoices', 'customers'));
    }

    public function create(Request $request)
    {
        $saleOrder = null;
        $lines = [];
        $prefill = [];

        if ($request->filled('sales_order_id')) {
            $saleOrder = SaleOrder::with(['customer', 'lines.product'])->findOrFail($request->sales_order_id);
            $lines = $this->buildLines($saleOrder);

            if (collect($lines)->sum('remaining') <= 0) {
                return redirect()->route('sales.salesinvoices.create')
                    ->with('error', 'This order is already fully invoiced.');
            }

            $prefill = $this->prefillFromOrder($saleOrder);
        }

        $orders = $saleOrder
            ? collect()
            : SaleOrder::with('customer')
                ->whereIn('status', self::INVOICABLE_ORDER_STATUSES)
                ->orderByDesc('id')
                ->get();

        return view('sales.salesinvoices.create', compact('saleOrder', 'orders', 'lines', 'prefill'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $order = SaleOrder::with('lines')->findOrFail($data['sales_order_id']);

        $invoice = DB::transaction(function () use ($data, $order, $request) {
            $invoice = new SaleInvoice();
            $invoice->status = 'draft';
            $invoice->created_by = Auth::id();

            $this->persist($invoice, $order, $data, $request->input('items', []));

            if ($request->boolean('validate_now')) {
                $this->issue($invoice);
            }

            return $invoice;
        });

        return redirect()->route('sales.salesinvoices.show', $invoice)
            ->with('success', $invoice->status === 'issued'
                ? 'Invoice issued successfully.'
                : 'Invoice saved as draft.');
    }

    public function show(SaleInvoice $saleInvoice)
    {
        $saleInvoice->load(['customer', 'salesOrder', 'lines.product', 'createdBy']);

        return view('sales.salesinvoices.show', compact('saleInvoice'));
    }

    public function edit(SaleInvoice $saleInvoice)
    {
        if (!$saleInvoice->isDraft()) {
            return redirect()->route('sales.salesinvoices.show', $saleInvoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $saleOrder = SaleOrder::with(['customer', 'lines.product'])->findOrFail($saleInvoice->sales_order_id);
        $lines = $this->buildLines($saleOrder, $saleInvoice);
        $prefill = [];

        return view('sales.salesinvoices.edit', compact('saleInvoice', 'saleOrder', 'lines', 'prefill'));
    }

    public function update(Request $request, SaleInvoice $saleInvoice)
    {
        if (!$saleInvoice->isDraft()) {
            return redirect()->route('sales.salesinvoices.show', $saleInvoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $data = $this->validated($request);
        $data['sales_order_id'] = $saleInvoice->sales_order_id; // the order never changes
        $order = SaleOrder::with('lines')->findOrFail($saleInvoice->sales_order_id);

        DB::transaction(function () use ($saleInvoice, $order, $data, $request) {
            $this->persist($saleInvoice, $order, $data, $request->input('items', []));

            if ($request->boolean('validate_now')) {
                $this->issue($saleInvoice);
            }
        });

        return redirect()->route('sales.salesinvoices.show', $saleInvoice)
            ->with('success', $saleInvoice->status === 'issued'
                ? 'Invoice issued successfully.'
                : 'Invoice updated.');
    }

    public function destroy(SaleInvoice $saleInvoice)
    {
        if (!$saleInvoice->isDraft()) {
            return redirect()->route('sales.salesinvoices.index')
                ->with('error', 'Only draft invoices can be deleted. Cancel the invoice instead.');
        }

        DB::transaction(function () use ($saleInvoice) {
            $saleInvoice->lines()->delete();
            $saleInvoice->delete();
        });

        return redirect()->route('sales.salesinvoices.index')->with('success', 'Draft invoice deleted.');
    }

    /** Draft -> Issued. (Named issueInvoice because Controller already has a validate() method.) */
    public function issueInvoice(SaleInvoice $saleInvoice)
    {
        if (!$saleInvoice->isDraft()) {
            return back()->with('error', 'Only draft invoices can be issued.');
        }

        if ($saleInvoice->lines()->count() === 0) {
            return back()->with('error', 'This invoice has no lines.');
        }

        $this->issue($saleInvoice);

        return redirect()->route('sales.salesinvoices.show', $saleInvoice)->with('success', 'Invoice issued.');
    }

    public function cancel(Request $request, SaleInvoice $saleInvoice)
    {
        if (!in_array($saleInvoice->status, ['draft', 'issued'], true)) {
            return back()->with('error', 'This invoice cannot be cancelled.');
        }

        if ((float) $saleInvoice->amount_paid > 0) {
            return back()->with('error', 'An invoice with recorded payments cannot be cancelled.');
        }

        $request->validate(['cancellation_reason' => 'nullable|string|max:255']);

        $saleInvoice->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('cancellation_reason'),
            'balance_due' => 0,
        ]);

        return redirect()->route('sales.salesinvoices.show', $saleInvoice)->with('success', 'Invoice cancelled.');
    }

    public function recordPayment(Request $request, SaleInvoice $saleInvoice)
    {
        if (!in_array($saleInvoice->status, ['issued', 'partial'], true)) {
            return back()->with('error', 'Payments can only be recorded on issued invoices.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $amount = round((float) $request->amount, 2);
        $balance = round((float) $saleInvoice->total - (float) $saleInvoice->amount_paid, 2);

        if ($amount > $balance + 0.005) {
            return back()->withInput()->with('error', 'The amount exceeds the balance due (' . number_format($balance, 2) . ').');
        }

        $paid = round((float) $saleInvoice->amount_paid + $amount, 2);
        $newBalance = round((float) $saleInvoice->total - $paid, 2);
        $isPaid = $newBalance <= 0.004;

        $saleInvoice->update([
            'amount_paid' => $paid,
            'balance_due' => max(0, $newBalance),
            'status' => $isPaid ? 'paid' : 'partial',
            'paid_at' => $isPaid ? now() : null,
            'payment_method' => $request->input('payment_method') ?: $saleInvoice->payment_method,
        ]);

        return redirect()->route('sales.salesinvoices.show', $saleInvoice)
            ->with('success', $isPaid ? 'Invoice fully paid.' : 'Payment recorded.');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    private function validated(Request $request): array
    {
        return $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'reference' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.order_line_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);
    }

    private function prefillFromOrder(SaleOrder $order): array
    {
        return [
            'currency' => $order->currency,
            'payment_terms' => $order->payment_terms ?? null,
            'billing_address' => $order->billing_address ?? ($order->customer->address ?? null),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ];
    }

    /** Quantity of an order line already invoiced on non-cancelled invoices. */
    private function invoicedQty(int $orderLineId, ?int $excludeInvoiceId = null): float
    {
        return (float) SalesInvoiceLine::where('order_line_id', $orderLineId)
            ->whereHas('invoice', function ($q) use ($excludeInvoiceId) {
                $q->where('status', '!=', 'cancelled');
                if ($excludeInvoiceId) {
                    $q->where('id', '!=', $excludeInvoiceId);
                }
            })
            ->sum('quantity');
    }

    /** Rows shown in the form: one per order line with ordered / invoiced / remaining. */
    private function buildLines(SaleOrder $order, ?SaleInvoice $invoice = null): array
    {
        $current = $invoice
            ? $invoice->lines()->get()->pluck('quantity', 'order_line_id')
            : collect();

        $rows = [];

        foreach ($order->lines as $ol) {
            $ordered = (float) $ol->quantity;
            $invoiced = $this->invoicedQty($ol->id, $invoice?->id);
            $remaining = max(0, $ordered - $invoiced);

            $rows[] = [
                'order_line_id' => $ol->id,
                'product' => $ol->product->name ?? null,
                'description' => $ol->description ?? ($ol->product->name ?? null),
                'unit' => $ol->unit ?? null,
                'ordered' => $ordered,
                'invoiced' => $invoiced,
                'remaining' => $remaining,
                'quantity' => $invoice ? (float) ($current[$ol->id] ?? 0) : $remaining,
                'price' => (float) $ol->price,
                'discount_percent' => (float) ($ol->discount_percent ?? 0),
                'tax_rate' => (float) ($ol->tax_rate ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * Recomputes everything server-side from the order lines (prices are never
     * taken from the browser), then saves the invoice header and its lines.
     */
    private function persist(SaleInvoice $invoice, SaleOrder $order, array $data, array $items): void
    {
        $orderLines = $order->lines->keyBy('id');
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

            $remaining = (float) $ol->quantity
                - $this->invoicedQty($ol->id, $invoice->exists ? $invoice->id : null);

            if ($qty > $remaining + 0.0005) {
                throw ValidationException::withMessages([
                    "items.$i.quantity" => 'Quantity exceeds what remains to be invoiced ('
                        . rtrim(rtrim(number_format($remaining, 3, '.', ''), '0'), '.') . ').',
                ]);
            }

            $price = (float) $ol->price;
            $disc = (float) ($ol->discount_percent ?? 0);
            $rate = (float) ($ol->tax_rate ?? 0);

            $gross = $qty * $price;
            $lineDisc = $gross * $disc / 100;
            $lineTotal = $gross - $lineDisc;
            $lineTax = $lineTotal * $rate / 100;

            $subtotal += $lineTotal;
            $tax += $lineTax;
            $discountTotal += $lineDisc;

            $computed[] = [
                'order_line_id' => $ol->id,
                'product_id' => $ol->product_id ?? null,
                'description' => $ol->description ?? ($ol->product->name ?? null),
                'unit' => $ol->unit ?? null,
                'quantity' => $qty,
                'price' => $price,
                'discount_percent' => $disc,
                'tax_rate' => $rate,
                'tax_amount' => round($lineTax, 2),
                'total' => round($lineTotal, 2),
                'sort_order' => count($computed),
            ];
        }

        if (empty($computed)) {
            throw ValidationException::withMessages([
                'items' => 'Enter a quantity greater than zero on at least one line.',
            ]);
        }

        $invoice->fill([
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'currency' => $order->currency ?? 'MAD',
            'date' => $data['date'],
            'due_date' => $data['due_date'] ?? null,
            'reference' => $data['reference'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'internal_notes' => $data['internal_notes'] ?? null,
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'tax' => round($tax, 2),
            'total' => round($subtotal + $tax, 2),
        ]);

        $invoice->amount_paid = $invoice->amount_paid ?? 0;
        $invoice->balance_due = round((float) $invoice->total - (float) $invoice->amount_paid, 2);

        if (!$invoice->invoice_number) {
            $invoice->invoice_number = SaleInvoice::generateNumber();
        }

        $invoice->save();

        $invoice->lines()->delete();
        foreach ($computed as $row) {
            $invoice->lines()->create($row);
        }
    }

    private function issue(SaleInvoice $invoice): void
    {
        $invoice->status = 'issued';
        $invoice->issued_at = now();

        if (!$invoice->due_date) {
            $invoice->due_date = $invoice->date->copy()->addDays(30);
        }

        $invoice->save();
    }
}
