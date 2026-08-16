<?php

namespace App\Http\Controllers\Purchases;

use App\Models\Purchases\PurchaseRequest;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['supplier', 'requestedBy', 'approvedBy']);

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

        $purchaseRequests = $query->latest('date')->paginate(15);

        return view('purchases.purchasesrequests.index', compact('purchaseRequests'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::select('id', 'name', 'price')->orderBy('name')->get();

        return view('purchases.purchase-requests.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:date',
            'priority' => 'required|in:low,medium,high,urgent',
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
        $validated['status'] = $request->input('status', 'draft');

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('purchase-requests', 'public');
        }

        $lines = $validated['lines'];
        unset($validated['lines']);

        $purchaseRequest = PurchaseRequest::create($validated);

        foreach ($lines as $line) {
            $purchaseRequest->lines()->create($line);
        }

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
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::select('id', 'name', 'price')->orderBy('name')->get();
        $purchaseRequest->load('lines');

        return view('purchases.purchase-requests.edit', compact('purchaseRequest', 'suppliers', 'products'));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,pending,approved,rejected,ordered,completed',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string|max:500',
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

        $purchaseRequest->update($validated);

        // Replace all lines: delete old ones, insert submitted ones
        $purchaseRequest->lines()->delete();
        foreach ($lines as $line) {
            $purchaseRequest->lines()->create($line);
        }

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

    private function generatePrNumber(): string
    {
        $year = now()->format('Y');
        $lastPr = PurchaseRequest::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $lastPr ? ((int) substr($lastPr->pr_number, -5)) + 1 : 1;

        return sprintf('PR-%s-%05d', $year, $nextNumber);
    }
}