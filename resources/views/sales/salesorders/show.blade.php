@extends('layouts.app')

@section('content')
    @php
        $o = $saleOrder;
        $color = \App\Models\Sales\SaleOrder::STATUS_COLORS[$o->status] ?? 'secondary';
        $qty = fn($v) => rtrim(rtrim(number_format($v, 3), '0'), '.');
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">
                Sales Order {{ $o->order_number }}
                <span class="badge bg-{{ $color }} fs-6 align-middle">{{ ucfirst($o->status) }}</span>
            </h1>

            <div class="d-flex gap-2">
                @if($o->status === 'draft')
                    <a href="{{ route('sales.salesorders.edit', $o) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <form action="{{ route('sales.salesorders.confirm', $o) }}" method="POST"
                        onsubmit="return confirm('Confirm this order?');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Confirm</button>
                    </form>
                @endif

                @if($o->status === 'confirmed')
                    <form action="{{ route('sales.salesorders.process', $o) }}" method="POST"
                        onsubmit="return confirm('Start processing this order?');">
                        @csrf
                        <button type="submit" class="btn btn-info"><i class="bi bi-gear"></i> Start processing</button>
                    </form>
                @endif

                @if(in_array($o->status, ['confirmed', 'processing']))
                    <form action="{{ route('sales.salesorders.deliver', $o) }}" method="POST"
                        onsubmit="return confirm('Mark this order as fully delivered?');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-truck"></i> Mark as delivered</button>
                    </form>
                @endif

                <a href="{{ route('sales.salesorders.index') }}" class="btn btn-outline-secondary">Back</a>
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

        @if($o->is_late)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                This order was expected on {{ $o->expected_delivery_date->format('Y-m-d') }} and has not been delivered yet.
            </div>
        @endif

        @if($o->status === 'cancelled')
            <div class="alert alert-danger">
                <i class="bi bi-x-octagon"></i> Cancelled on {{ optional($o->cancelled_at)->format('Y-m-d H:i') }}.
                @if($o->cancellation_reason) Reason: {{ $o->cancellation_reason }} @endif
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Customer</small>{{ $o->customer->name ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Customer PO reference</small>{{ $o->reference ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Currency</small>{{ $o->currency }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Order date</small>{{ optional($o->date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Expected delivery</small>{{ optional($o->expected_delivery_date)->format('Y-m-d') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Payment terms</small>{{ $o->payment_terms ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Delivery terms</small>{{ $o->delivery_terms ?? '—' }}</div>

                <div class="col-md-6"><small class="text-muted d-block">Billing address</small>{!! nl2br(e($o->billing_address ?: '—')) !!}</div>
                <div class="col-md-6"><small class="text-muted d-block">Shipping address</small>{!! nl2br(e($o->shipping_address ?: '—')) !!}</div>

                <div class="col-md-3"><small class="text-muted d-block">Created by</small>{{ $o->createdBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Confirmed at</small>{{ optional($o->confirmed_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Delivered at</small>{{ optional($o->delivered_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Quotation</small>
                    @if($o->quotation)
                        <a href="{{ route('sales.salesquotations.show', $o->quotation) }}">{{ $o->quotation->quote_number }}</a>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Order lines</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Delivered</th>
                                <th class="text-end">Invoiced</th>
                                <th>Unit</th>
                                <th class="text-end">Unit price</th>
                                <th class="text-end">Disc. %</th>
                                <th class="text-end">VAT %</th>
                                <th class="text-end">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($o->items as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? '—' }}</td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ $qty($line->quantity) }}</td>
                                    <td class="text-end">{{ $qty($line->delivered_quantity) }}</td>
                                    <td class="text-end">{{ $qty($line->invoiced_quantity) }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($line->price, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->discount_percent, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->tax_rate, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">No lines.</td></tr>
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
                        <p class="text-muted">{{ $o->notes ?: '—' }}</p>
                        <h6 class="fw-semibold">Terms &amp; conditions</h6>
                        <p class="text-muted">{{ $o->terms_conditions ?: '—' }}</p>
                        <h6 class="fw-semibold">Internal notes</h6>
                        <p class="text-muted mb-0">{{ $o->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format($o->subtotal, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong>{{ number_format($o->discount_amount, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><strong>{{ number_format($o->tax, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Shipping cost</span><strong>{{ number_format($o->shipping_cost, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span><strong>{{ number_format($o->total, 2) }} {{ $o->currency }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array($o->status, ['draft', 'confirmed', 'processing']))
            <div class="card shadow-sm mb-5">
                <div class="card-header fw-semibold">Cancel this order</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.salesorders.cancel', $o) }}"
                        class="row g-2 align-items-end" onsubmit="return confirm('Cancel this order?');">
                        @csrf
                        <div class="col-md-9">
                            <label class="form-label mb-1">Cancellation reason</label>
                            <input type="text" name="cancellation_reason" maxlength="255" class="form-control"
                                value="{{ old('cancellation_reason') }}" placeholder="e.g. Customer changed their mind">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Cancel order
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
