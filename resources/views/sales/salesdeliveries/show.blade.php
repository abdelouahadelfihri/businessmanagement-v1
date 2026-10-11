@extends('layouts.app')

@section('content')
    @php
        $d = $saleDelivery;
        $color = \App\Models\Sales\SaleDelivery::STATUS_COLORS[$d->status] ?? 'secondary';
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') ?: '0';
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">
                Sales Delivery {{ $d->delivery_number }}
                <span class="badge bg-{{ $color }} fs-6 align-middle">{{ ucfirst($d->status) }}</span>
            </h1>

            <div class="d-flex gap-2">
                @if($d->status === 'draft')
                    <a href="{{ route('sales.salesdeliveries.edit', $d) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <form action="{{ route('sales.salesdeliveries.validate', $d) }}" method="POST"
                        onsubmit="return confirm('Validate this delivery? The stock will be updated.');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Validate</button>
                    </form>
                @endif

                <a href="{{ route('sales.salesdeliveries.index') }}" class="btn btn-outline-secondary">Back</a>
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

        @if($d->status === 'cancelled')
            <div class="alert alert-danger">
                <i class="bi bi-x-octagon"></i> Cancelled on {{ optional($d->cancelled_at)->format('Y-m-d H:i') }}.
                @if($d->cancellation_reason) Reason: {{ $d->cancellation_reason }} @endif
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Sales order</small>
                    @if($d->salesOrder)
                        <a href="{{ route('sales.salesorders.show', $d->salesOrder) }}">{{ $d->salesOrder->order_number }}</a>
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-4"><small class="text-muted d-block">Customer</small>{{ $d->salesOrder->customer->name ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Warehouse</small>{{ $d->warehouse->name ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Delivery date</small>{{ optional($d->date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Carrier</small>{{ $d->carrier ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Tracking number</small>{{ $d->tracking_number ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Currency</small>{{ $d->currency }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Recipient</small>{{ $d->recipient_name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Recipient phone</small>{{ $d->recipient_phone ?? '—' }}</div>
                <div class="col-md-6"><small class="text-muted d-block">Shipping address</small>{!! nl2br(e($d->shipping_address ?: '—')) !!}</div>

                <div class="col-md-3"><small class="text-muted d-block">Created by</small>{{ $d->createdBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Validated at</small>{{ optional($d->validated_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Cancelled at</small>{{ optional($d->cancelled_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Stock impact</small>
                    @if($d->stockMovements->isNotEmpty())
                        <span class="badge bg-dark">{{ $d->stockMovements->count() }} stock movement(s)</span>
                    @else
                        <span class="text-muted">None</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Delivered lines</div>
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
                            @forelse($d->lines as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? '—' }}</td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ $qty($line->quantity) }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
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
                        <p class="text-muted">{{ $d->notes ?: '—' }}</p>
                        <h6 class="fw-semibold">Internal notes</h6>
                        <p class="text-muted mb-0">{{ $d->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format($d->subtotal, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><strong>{{ number_format($d->tax, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span><strong>{{ number_format($d->total, 2) }} {{ $d->currency }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array($d->status, ['draft', 'validated']))
            <div class="card shadow-sm mb-5">
                <div class="card-header fw-semibold">Cancel this delivery</div>
                <div class="card-body">
                    @if($d->status === 'validated')
                        <div class="alert alert-warning py-2">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Cancelling a validated delivery <strong>puts the goods back in stock</strong> and
                            re-opens the delivered quantities on the order.
                        </div>
                    @endif
                    <form method="POST" action="{{ route('sales.salesdeliveries.cancel', $d) }}"
                        class="row g-2 align-items-end" onsubmit="return confirm('Cancel this delivery?');">
                        @csrf
                        <div class="col-md-9">
                            <label class="form-label mb-1">Cancellation reason</label>
                            <input type="text" name="cancellation_reason" maxlength="255" class="form-control"
                                value="{{ old('cancellation_reason') }}" placeholder="e.g. Goods returned by the carrier">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Cancel delivery
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
