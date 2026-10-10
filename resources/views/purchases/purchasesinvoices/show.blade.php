@extends('layouts.app')

@section('content')
    @php
        $statusColors = ['draft' => 'secondary', 'validated' => 'success', 'cancelled' => 'danger'];
        $paymentColors = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'];
        $i = $purchaseInvoice;
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">
                Purchase Invoice {{ $i->invoice_number }}
                <span class="badge bg-{{ $statusColors[$i->status] ?? 'secondary' }} fs-6 align-middle">{{ ucfirst($i->status) }}</span>
                <span class="badge bg-{{ $paymentColors[$i->payment_status] ?? 'secondary' }} fs-6 align-middle">{{ ucfirst($i->payment_status) }}</span>
            </h1>

            <div class="d-flex gap-2">
                @if($i->status === 'draft')
                    <a href="{{ route('purchases.purchasesinvoices.edit', $i) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <form action="{{ route('purchases.purchasesinvoices.validate', $i) }}" method="POST"
                        onsubmit="return confirm('Validate this invoice?');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Validate</button>
                    </form>

                    <form action="{{ route('purchases.purchasesinvoices.cancel', $i) }}" method="POST"
                        onsubmit="return confirm('Cancel this invoice?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Cancel</button>
                    </form>
                @endif

                <a href="{{ route('purchases.purchasesinvoices.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if($i->is_overdue)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                This invoice was due on {{ $i->due_date->format('Y-m-d') }} and is not fully paid.
            </div>
        @endif

        @if($i->status === 'validated' && $i->affectsStock())
            <div class="alert alert-info">
                <i class="bi bi-box-seam"></i> Direct invoice: stock was increased in
                <strong>{{ $i->warehouse->name ?? '—' }}</strong> when this invoice was validated.
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Supplier</small>{{ $i->supplier->name ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Purchase order</small>{{ $i->purchaseOrder->po_number ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Purchase receipt</small>{{ $i->purchaseReceipt->receipt_number ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Invoice date</small>{{ optional($i->date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Due date</small>{{ optional($i->due_date)->format('Y-m-d') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Supplier invoice n°</small>{{ $i->supplier_invoice_number ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Payment terms</small>{{ $i->payment_terms ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Warehouse</small>{{ $i->warehouse->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Created by</small>{{ $i->createdBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Validated by</small>{{ $i->validatedBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Validated at</small>{{ optional($i->validated_at)->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Invoice lines</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th>Unit</th>
                                <th class="text-end">Unit price</th>
                                <th class="text-end">Disc. %</th>
                                <th class="text-end">VAT %</th>
                                <th class="text-end">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($i->items as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? '—' }}</td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($line->quantity, 3), '0'), '.') }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($line->price, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->discount_percent, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->tax_rate, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No lines.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold">Notes</h6>
                        <p class="text-muted">{{ $i->notes ?: '—' }}</p>
                        <h6 class="fw-semibold">Internal notes</h6>
                        <p class="text-muted mb-0">{{ $i->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format($i->subtotal, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong>{{ number_format($i->discount_amount, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><strong>{{ number_format($i->tax, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Shipping cost</span><strong>{{ number_format($i->shipping_cost, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span><strong>{{ number_format($i->total, 2) }} {{ $i->currency }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2"><span>Paid</span><strong>{{ number_format($i->amount_paid, 2) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Balance due</span><strong>{{ number_format($i->balance_due, 2) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        @if($i->status === 'validated' && $i->balance_due > 0)
            <div class="card shadow-sm mb-5">
                <div class="card-header fw-semibold">Record a payment</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('purchases.purchasesinvoices.payment', $i) }}"
                        class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label mb-1">Amount *</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $i->balance_due }}" name="amount"
                                class="form-control" value="{{ old('amount', $i->balance_due) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Method *</label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(\App\Models\Purchases\PurchaseInvoice::PAYMENT_METHODS as $key => $label)
                                    <option value="{{ $key }}" @selected(old('payment_method') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Payment date *</label>
                            <input type="date" name="paid_at" class="form-control"
                                value="{{ old('paid_at', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-cash-coin"></i> Record payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="mb-5"></div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
