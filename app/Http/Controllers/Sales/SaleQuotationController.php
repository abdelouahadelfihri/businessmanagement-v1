<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Product;
use App\Models\Sales\SaleQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleQuotationController extends Controller
{
    public function index(Request $request)
    {
        $saleQuotations = SaleQuotation::with('customer')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('quote_number', 'like', $term)
                        ->orWhere('reference', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('date')
            ->latest('id')
            ->paginate(15)
            ->appends($request->query());

        return view('sales.salesquotations.index', compact('saleQuotations'));
    }

    public function create(Request $request)
    {
        $prefill = $request->filled('customer_id') ? ['customer_id' => $request->customer_id] : [];

        return view('sales.salesquotations.create', $this->formData() + compact('prefill'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $quote = DB::transaction(fn () => $this->persist(new SaleQuotation(), $data));

        if ($request->boolean('send_now')) {
            return $this->send($quote);
        }

        return redirect()->route('sales.salesquotations.index')
            ->with('success', 'Quotation ' . $quote->quote_number . ' created.');
    }

    public function show(SaleQuotation $saleQuotation)
    {
        $saleQuotation->load(['customer', 'items.product', 'createdBy']);

        return view('sales.salesquotations.show', compact('saleQuotation'));
    }

    public function edit(SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'draft') {
            return redirect()->route('sales.salesquotations.show', $saleQuotation)
                ->with('error', 'Only draft quotations can be edited.');
        }

        $saleQuotation->load('items');

        return view('sales.salesquotations.edit', $this->formData() + compact('saleQuotation'));
    }

    public function update(Request $request, SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'draft') {
            return redirect()->route('sales.salesquotations.show', $saleQuotation)
                ->with('error', 'Only draft quotations can be edited.');
        }

        $data = $request->validate($this->rules());

        DB::transaction(fn () => $this->persist($saleQuotation, $data));

        if ($request->boolean('send_now')) {
            return $this->send($saleQuotation);
        }

        return redirect()->route('sales.salesquotations.index')
            ->with('success', 'Quotation ' . $saleQuotation->quote_number . ' updated.');
    }

    public function destroy(SaleQuotation $saleQuotation)
    {
        if (!in_array($saleQuotation->status, ['draft', 'cancelled'], true)) {
            return back()->with('error', 'Only draft or cancelled quotations can be deleted.');
        }

        DB::transaction(function () use ($saleQuotation) {
            $saleQuotation->items()->delete();
            $saleQuotation->delete();
        });

        return redirect()->route('sales.salesquotations.index')
            ->with('success', 'Quotation deleted.');
    }

    // ------------------------------------------------------------ status workflow

    /** draft -> sent */
    public function send(SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'draft') {
            return back()->with('error', 'Only draft quotations can be marked as sent.');
        }

        $saleQuotation->update(['status' => 'sent', 'sent_at' => now()]);

        return redirect()->route('sales.salesquotations.show', $saleQuotation)
            ->with('success', 'Quotation marked as sent.');
    }

    /** sent -> accepted */
    public function accept(SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'sent') {
            return back()->with('error', 'Only sent quotations can be accepted.');
        }

        $saleQuotation->update(['status' => 'accepted', 'accepted_at' => now()]);

        return redirect()->route('sales.salesquotations.show', $saleQuotation)
            ->with('success', 'Quotation accepted.');
    }

    /** sent -> rejected */
    public function reject(Request $request, SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'sent') {
            return back()->with('error', 'Only sent quotations can be rejected.');
        }

        $data = $request->validate(['rejection_reason' => ['nullable', 'string', 'max:255']]);

        $saleQuotation->update([
            'status'           => 'rejected',
            'rejected_at'      => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        return redirect()->route('sales.salesquotations.show', $saleQuotation)
            ->with('success', 'Quotation rejected.');
    }

    /** draft | sent -> cancelled */
    public function cancel(SaleQuotation $saleQuotation)
    {
        if (!in_array($saleQuotation->status, ['draft', 'sent'], true)) {
            return back()->with('error', 'Only draft or sent quotations can be cancelled.');
        }

        $saleQuotation->update(['status' => 'cancelled']);

        return redirect()->route('sales.salesquotations.show', $saleQuotation)
            ->with('success', 'Quotation cancelled.');
    }

    // ------------------------------------------------------------------ helpers

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'products'  => Product::orderBy('name')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'customer_id'      => ['required', 'exists:customers,id'],
            'reference'        => ['nullable', 'string', 'max:100'],
            'date'             => ['required', 'date'],
            'valid_until'      => ['nullable', 'date', 'after_or_equal:date'],
            'payment_terms'    => ['nullable', 'string', 'max:150'],
            'delivery_terms'   => ['nullable', 'string', 'max:150'],
            'currency'         => ['required', Rule::in(['MAD', 'EUR', 'USD'])],
            'discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'    => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'internal_notes'   => ['nullable', 'string'],

            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['nullable', 'integer'],
            'items.*.description'      => ['required', 'string', 'max:255'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.001'],
            'items.*.unit'             => ['nullable', 'string', 'max:30'],
            'items.*.price'            => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'between:0,100'],
            'items.*.tax_rate'         => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    private function persist(SaleQuotation $quote, array $data): SaleQuotation
    {
        [$lines, $subtotal, $tax] = $this->buildLines($data['items']);

        $discount = (float) ($data['discount_amount'] ?? 0);
        $shipping = (float) ($data['shipping_cost'] ?? 0);
        $total    = round($subtotal - $discount + $tax + $shipping, 2);

        $quote->fill([
            'customer_id'      => $data['customer_id'],
            'reference'        => $data['reference'] ?? null,
            'date'             => $data['date'],
            'valid_until'      => $data['valid_until'] ?? null,
            'payment_terms'    => $data['payment_terms'] ?? null,
            'delivery_terms'   => $data['delivery_terms'] ?? null,
            'currency'         => $data['currency'],
            'notes'            => $data['notes'] ?? null,
            'terms_conditions' => $data['terms_conditions'] ?? null,
            'internal_notes'   => $data['internal_notes'] ?? null,
            'subtotal'         => $subtotal,
            'discount_amount'  => $discount,
            'tax'              => $tax,
            'shipping_cost'    => $shipping,
            'total'            => $total,
        ]);

        if (!$quote->exists) {
            $quote->fill([
                'status'     => 'draft',
                'created_by' => Auth::id(),
            ]);
        }

        $quote->save();

        $quote->items()->delete();
        $quote->items()->createMany($lines);

        return $quote;
    }

    private function buildLines(array $items): array
    {
        $subtotal = 0;
        $tax = 0;
        $lines = [];

        foreach (array_values($items) as $i => $item) {
            $qty   = (float) $item['quantity'];
            $price = (float) $item['price'];
            $disc  = (float) ($item['discount_percent'] ?? 0);
            $rate  = (float) ($item['tax_rate'] ?? 0);
            $total = round($qty * $price * (1 - $disc / 100), 2);

            $subtotal += $total;
            $tax += $total * $rate / 100;

            $lines[] = [
                'product_id'       => ($item['product_id'] ?? null) ?: null,
                'description'      => $item['description'],
                'unit'             => $item['unit'] ?? null,
                'quantity'         => $qty,
                'price'            => $price,
                'discount_percent' => $disc,
                'tax_rate'         => $rate,
                'total'            => $total,
                'sort_order'       => $i,
            ];
        }

        return [$lines, round($subtotal, 2), round($tax, 2)];
    }
}
