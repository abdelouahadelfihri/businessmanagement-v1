<?php

namespace App\Http\Controllers\Purchases;

use App\Models\Purchases\PurchaseRequest;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['supplier:id,name', 'requestedBy:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('pr_number', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // CHANGED: withQueryString() keeps the filters when clicking page 2, 3...
        $purchaseRequests = $query->latest('date')->paginate(15)->withQueryString();

        return view('purchases.purchasesrequests.index', compact('purchaseRequests'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::select('id', 'name')->orderBy('name')->get();

        return view('purchases.purchasesrequests.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'nullable|in:draft,pending', // CHANGED: client can no longer post "approved"
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'nullable|exists:products,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.unit' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $validated['pr_number'] = $this->generatePrNumber();
        $validated['requested_by'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'draft';

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('purchase-requests', 'public');
        }

        $lines = $validated['lines'];
        unset($validated['lines']);

        // CHANGED: transaction so a failure can't leave a request without its lines
        $purchaseRequest = DB::transaction(function () use ($validated, $lines) {
            $purchaseRequest = PurchaseRequest::create($validated);

            $total = $this->syncLines($purchaseRequest, $lines);

            $purchaseRequest->update(['total_amount' => $total]);

            return $purchaseRequest;
        });

        return redirect()
            ->route('purchases.purchasesrequests.show', $purchaseRequest)
            ->with('success', 'Purchase request created successfully.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['supplier', 'requestedBy', 'approvedBy', 'lines']);
        return view('purchases.purchasesrequests.show', compact('purchaseRequest'));
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        // CHANGED: only drafts can be edited (matches the buttons in the index view)
        abort_unless($purchaseRequest->status === 'draft', 403, 'Only draft requests can be edited.');

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::select('id', 'name')->orderBy('name')->get();
        $purchaseRequest->load('lines');

        $existingLines = $purchaseRequest->lines->map(function ($line) {
            return [
                'product_id' => $line->product_id,
                'description' => $line->description,
                'unit' => $line->unit,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
            ];
        })->values();

        // CHANGED: view name now matches the other actions (purchasesrequests)
        return view('purchases.purchasesrequests.edit', compact('purchaseRequest', 'suppliers', 'products', 'existingLines'));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        abort_unless($purchaseRequest->status === 'draft', 403, 'Only draft requests can be edited.'); // CHANGED

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,pending', // CHANGED: approval only goes through approve()/reject()
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'nullable|exists:products,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.unit' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($request->hasFile('attachment')) {
            if ($purchaseRequest->attachment) {
                Storage::disk('public')->delete($purchaseRequest->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('purchase-requests', 'public');
        }

        $lines = $validated['lines'];
        unset($validated['lines']);

        // CHANGED: transaction + total recalculated
        DB::transaction(function () use ($purchaseRequest, $validated, $lines) {
            $purchaseRequest->update($validated);

            // Replace all lines: delete old ones, insert submitted ones
            $purchaseRequest->lines()->delete();
            $total = $this->syncLines($purchaseRequest, $lines);

            $purchaseRequest->update(['total_amount' => $total]);
        });

        return redirect()
            ->route('purchases.purchasesrequests.show', $purchaseRequest)
            ->with('success', 'Purchase request updated successfully.');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->attachment) {
            Storage::disk('public')->delete($purchaseRequest->attachment);
        }

        $purchaseRequest->delete();

        return redirect()
            ->route('purchases.purchasesrequests.index')
            ->with('success', 'Purchase request deleted successfully.');
    }

    public function approve(PurchaseRequest $purchaseRequest)
    {
        abort_unless($purchaseRequest->status === 'pending', 403, 'Only pending requests can be approved.'); // CHANGED

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Purchase request approved.');
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        abort_unless($purchaseRequest->status === 'pending', 403, 'Only pending requests can be rejected.'); // CHANGED

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $purchaseRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Purchase request rejected.');
    }

    /**
     * CHANGED (new helper): creates the lines with total_price
     * and returns the sum for the request's total_amount.
     */
    private function syncLines(PurchaseRequest $purchaseRequest, array $lines): float
    {
        $total = 0;

        foreach ($lines as $line) {
            $line['total_price'] = round($line['quantity'] * $line['unit_price'], 2);
            $total += $line['total_price'];

            $purchaseRequest->lines()->create($line);
        }

        return $total;
    }

    private function generatePrNumber(): string
    {
        $year = now()->format('Y');
        $lastPr = PurchaseRequest::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $lastPr ? ((int) substr($lastPr->pr_number, -5)) + 1 : 1;

        return sprintf('PR-%s-%05d', $year, $nextNumber);
    }
}