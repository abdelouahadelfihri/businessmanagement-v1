<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Product;
use App\Models\MasterData\Supplier;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($w) use ($s) {
                    $w->where('po_number', 'like', "%{$s}%")
                        ->orWhere('supplier_reference', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('payment_status'), fn($q) => $q->where('payment_status', $request->payment_status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('purchases.purchasesorders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        return view('purchases.purchasesorders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $order = DB::transaction(function () use ($request, $data) {
            $items = $data['items'];
            unset($data['items']);

            $data['status'] = $request->boolean('submit')
                ? PurchaseOrder::STATUS_PENDING_APPROVAL
                : PurchaseOrder::STATUS_DRAFT;
            $data['created_by'] = auth()->id();

            $order = PurchaseOrder::create($data);
            $this->syncLines($order, $items);

            return $order;
        });

        return redirect()
            ->route('purchases.purchasesorders.show', $order)
            ->with('success', "Purchase order {$order->po_number} created.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'request', 'items.product', 'creator', 'approver']);

        return view('purchases.purchasesorders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return redirect()
                ->route('purchases.purchasesorders.show', $purchaseOrder)
                ->with('error', 'This purchase order can no longer be edited.');
        }

        $purchaseOrder->load('items');

        return view('purchases.purchasesorders.edit', $this->formData() + compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return redirect()
                ->route('purchases.purchasesorders.show', $purchaseOrder)
                ->with('error', 'This purchase order can no longer be edited.');
        }

        $data = $request->validate($this->rules());

        DB::transaction(function () use ($request, $data, $purchaseOrder) {
            $items = $data['items'];
            unset($data['items']);

            if ($request->boolean('submit') && $purchaseOrder->status === PurchaseOrder::STATUS_DRAFT) {
                $data['status'] = PurchaseOrder::STATUS_PENDING_APPROVAL;
            }

            $purchaseOrder->update($data);
            $this->syncLines($purchaseOrder, $items);
        });

        return redirect()
            ->route('purchases.purchasesorders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])) {
            return back()->with('error', 'Only draft or cancelled orders can be deleted.');
        }

        $purchaseOrder->delete(); // soft delete, the PO number is never reused

        return redirect()
            ->route('purchases.purchasesorders.index')
            ->with('success', 'Purchase order deleted.');
    }

    // ---------- Workflow actions ----------

    public function submit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'Only draft orders can be submitted.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_PENDING_APPROVAL]);

        return back()->with('success', 'Purchase order submitted for approval.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Only orders pending approval can be approved.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase order approved.');
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_APPROVED) {
            return back()->with('error', 'Only approved orders can be marked as sent.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_SENT,
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Purchase order marked as sent to the supplier.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate(['cancellation_reason' => ['required', 'string', 'max:1000']]);

        if (
            in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_RECEIVED,
                PurchaseOrder::STATUS_CLOSED,
                PurchaseOrder::STATUS_CANCELLED,
            ])
        ) {
            return back()->with('error', 'This purchase order can no longer be cancelled.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return back()->with('success', 'Purchase order cancelled.');
    }

    // ---------- Helpers ----------

    private function formData(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'purchaseRequests' => PurchaseRequest::where('status', 'approved')->orderByDesc('id')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'request_id' => ['nullable', 'exists:purchase_requests,id'],
            'supplier_reference' => ['nullable', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'currency' => ['required', 'string', 'size:3'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'shipping_method' => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'between:0,100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    private function syncLines(PurchaseOrder $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? null,
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'tax_rate' => $item['tax_rate'] ?? 0,
            ]);
        }
        // line_total and the PO totals are recalculated by the PurchaseOrderLine model hooks
    }
}