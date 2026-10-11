@extends('layouts.app')

@section('content')
    @php
        $statusColors = ['draft' => 'secondary', 'sent' => 'primary', 'accepted' => 'success', 'rejected' => 'danger', 'cancelled' => 'dark'];
        $q = $saleQuotation;
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">
                Sales Quotation {{ $q->quote_number }}
                <span class="badge bg-{{ $statusColors[$q->status] ?? 'secondary' }} fs-6 align-middle">{{ ucfirst($q->status) }}</span>
            </h1>

            <div class="d-flex gap-2">
                @if($q->status === 'draft')
                    <a href="{{ route('sales.salesquotations.edit', $q) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <form action="{{ route('sales.salesquotations.send', $q) }}" method="POST"
                        onsubmit="return confirm('Mark this quotation as sent?');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Mark as sent</button>
                    </form>
                @endif

                @if($q->status === 'sent')
                    <form action="{{ route('sales.salesquotations.accept', $q) }}" method="POST"
                        onsubmit="return confirm('Mark this quotation as accepted by the customer?');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Accepted</button>
                    </form>
                @endif

                @if(in_array($q->status, ['draft', 'sent']))
                    <form action="{{ route('sales.salesquotations.cancel', $q) }}" method="POST"
                        onsubmit="return confirm('Cancel this quotation?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Cancel</button>
                    </form>
                @endif

                <a href="{{ route('sales.salesquotations.index') }}" class="btn btn-outline-secondary">Back</a>
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

        @if($q->is_expired)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                This quotation expired on {{ $q->valid_until->format('Y-m-d') }} without an answer from the customer.
            </div>
        @endif

        @if($q->status === 'rejected')
            <div class="alert alert-danger">
                <i class="bi bi-x-octagon"></i> Rejected on {{ optional($q->rejected_at)->format('Y-m-d H:i') }}.
                @if($q->rejection_reason) Reason: {{ $q->rejection_reason }} @endif
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Customer</small>{{ $q->customer->name ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Customer reference</small>{{ $q->reference ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Currency</small>{{ $q->currency }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Quotation date</small>{{ optional($q->date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Valid until</small>{{ optional($q->valid_until)->format('Y-m-d') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Payment terms</small>{{ $q->payment_terms ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Delivery terms</small>{{ $q->delivery_terms ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Created by</small>{{ $q->createdBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Sent at</small>{{ optional($q->sent_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Accepted at</small>{{ optional($q->accepted_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Rejected at</small>{{ optional($q->rejected_at)->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Quotation lines</div>
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
                            @forelse($q->items as $line)
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
                        <p class="text-muted">{{ $q->notes ?: '—' }}</p>
                        <h6 class="fw-semibold">Terms &amp; conditions</h6>
                        <p class="text-muted">{{ $q->terms_conditions ?: '—' }}</p>
                        <h6 class="fw-semibold">Internal notes</h6>
                        <p class="text-muted mb-0">{{ $q->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format($q->subtotal, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong>{{ number_format($q->discount_amount, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><strong>{{ number_format($q->tax, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Shipping cost</span><strong>{{ number_format($q->shipping_cost, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span><strong>{{ number_format($q->total, 2) }} {{ $q->currency }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($q->status === 'sent')
            <div class="card shadow-sm mb-5">
                <div class="card-header fw-semibold">Customer declined?</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.salesquotations.reject', $q) }}"
                        class="row g-2 align-items-end" onsubmit="return confirm('Mark this quotation as rejected?');">
                        @csrf
                        <div class="col-md-9">
                            <label class="form-label mb-1">Rejection reason</label>
                            <input type="text" name="rejection_reason" maxlength="255" class="form-control"
                                value="{{ old('rejection_reason') }}" placeholder="e.g. Price too high">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-hand-thumbs-down"></i> Mark as rejected
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
