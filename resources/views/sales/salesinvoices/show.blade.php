@extends('layouts.app')

@section('content')
    @php
        $inv = $saleInvoice;
        $color = \App\Models\Sales\SaleInvoice::STATUS_COLORS[$inv->status] ?? 'secondary';
        $label = \App\Models\Sales\SaleInvoice::STATUSES[$inv->status] ?? ucfirst($inv->status);
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') ?: '0';
        $paymentMethods = ['Bank transfer', 'Cash', 'Cheque', 'Card', 'Other'];
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">
                Sales Invoice {{ $inv->invoice_number }}
                <span class="badge bg-{{ $color }} fs-6 align-middle">{{ $label }}</span>
                @if($inv->isOverdue())
                    <span class="badge bg-danger fs-6 align-middle">Overdue</span>
                @endif
            </h1>

            <div class="d-flex gap-2">
                @if($inv->status === 'draft')
                    <a href="{{ route('sales.salesinvoices.edit', $inv) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <form action="{{ route('sales.salesinvoices.validate', $inv) }}" method="POST"
                        onsubmit="return confirm('Issue this invoice? It will no longer be editable.');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Issue</button>
                    </form>
                @endif

                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <a href="{{ route('sales.salesinvoices.index') }}" class="btn btn-outline-secondary">Back</a>
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

        @if($inv->status === 'cancelled')
            <div class="alert alert-danger">
                <i class="bi bi-x-octagon"></i> Cancelled on {{ optional($inv->cancelled_at)->format('Y-m-d H:i') }}.
                @if($inv->cancellation_reason) Reason: {{ $inv->cancellation_reason }} @endif
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Sales order</small>
                    @if($inv->salesOrder)
                        <a href="{{ route('sales.salesorders.show', $inv->salesOrder) }}">{{ $inv->salesOrder->order_number }}</a>
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-4"><small class="text-muted d-block">Customer</small>{{ $inv->customer->name ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Customer reference</small>{{ $inv->reference ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Invoice date</small>{{ optional($inv->date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Due date</small>{{ optional($inv->due_date)->format('Y-m-d') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Payment terms</small>{{ $inv->payment_terms ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Payment method</small>{{ $inv->payment_method ?? '—' }}</div>

                <div class="col-md-6"><small class="text-muted d-block">Billing address</small>{!! nl2br(e($inv->billing_address ?: '—')) !!}</div>
                <div class="col-md-3"><small class="text-muted d-block">Currency</small>{{ $inv->currency }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Created by</small>{{ $inv->createdBy->name ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Issued at</small>{{ optional($inv->issued_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Fully paid at</small>{{ optional($inv->paid_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Cancelled at</small>{{ optional($inv->cancelled_at)->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Invoiced lines</div>
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
                            @forelse($inv->lines as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? '—' }}</td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ $qty($line->quantity) }}</td>
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
                        <p class="text-muted">{{ $inv->notes ?: '—' }}</p>
                        <h6 class="fw-semibold">Internal notes</h6>
                        <p class="text-muted mb-0">{{ $inv->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format($inv->subtotal, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2 text-muted"><span>Discounts included</span><span>{{ number_format($inv->discount_total, 2) }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><strong>{{ number_format($inv->tax, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span><strong>{{ number_format($inv->total, 2) }} {{ $inv->currency }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2"><span>Paid</span><strong class="text-success">{{ number_format($inv->amount_paid, 2) }}</strong></div>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Balance due</span><strong class="{{ $inv->balance_due > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($inv->balance_due, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Record a payment --}}
        @if(in_array($inv->status, ['issued', 'partial']))
            <div class="card shadow-sm mb-3 d-print-none">
                <div class="card-header fw-semibold">Record a payment</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.salesinvoices.payment', $inv) }}"
                        class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label mb-1">Amount *</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $inv->balance_due }}" name="amount"
                                class="form-control" value="{{ old('amount', $inv->balance_due) }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mb-1">Payment method</label>
                            <select name="payment_method" class="form-select">
                                <option value="">—</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" @selected(old('payment_method', $inv->payment_method) === $method)>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-cash-coin"></i> Record payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Cancel --}}
        @if(in_array($inv->status, ['draft', 'issued']) && (float) $inv->amount_paid == 0.0)
            <div class="card shadow-sm mb-5 d-print-none">
                <div class="card-header fw-semibold">Cancel this invoice</div>
                <div class="card-body">
                    @if($inv->status === 'issued')
                        <div class="alert alert-warning py-2">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Cancelling an issued invoice <strong>re-opens the invoiced quantities</strong> on the
                            order so they can be invoiced again.
                        </div>
                    @endif
                    <form method="POST" action="{{ route('sales.salesinvoices.cancel', $inv) }}"
                        class="row g-2 align-items-end" onsubmit="return confirm('Cancel this invoice?');">
                        @csrf
                        <div class="col-md-9">
                            <label class="form-label mb-1">Cancellation reason</label>
                            <input type="text" name="cancellation_reason" maxlength="255" class="form-control"
                                value="{{ old('cancellation_reason') }}" placeholder="e.g. Wrong customer, issued by mistake">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Cancel invoice
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
