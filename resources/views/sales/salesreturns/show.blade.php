@extends('layouts.app')

@section('content')
    @php
        $ret = $saleReturn;
        $color = \App\Models\Sales\SaleReturn::STATUS_COLORS[$ret->status] ?? 'secondary';
        $label = \App\Models\Sales\SaleReturn::STATUSES[$ret->status] ?? ucfirst($ret->status);
        $reasons = \App\Models\Sales\SaleReturn::REASONS;
        $conditions = \App\Models\Sales\SaleReturn::CONDITIONS;
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') ?: '0';
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">
                Sales Return {{ $ret->return_number }}
                <span class="badge bg-{{ $color }} fs-6 align-middle">{{ $label }}</span>
            </h1>

            <div class="d-flex gap-2">
                @if($ret->isDraft())
                    <a href="{{ route('sales.salesreturns.edit', $ret) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <form action="{{ route('sales.salesreturns.validate', $ret) }}" method="POST"
                        onsubmit="return confirm('Validate this return? Restocked goods will be put back into the warehouse.');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Validate</button>
                    </form>

                    <form action="{{ route('sales.salesreturns.destroy', $ret) }}" method="POST"
                        onsubmit="return confirm('Delete this draft return?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                @endif

                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <a href="{{ route('sales.salesreturns.index') }}" class="btn btn-outline-secondary">Back</a>
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

        @if($ret->status === 'cancelled')
            <div class="alert alert-danger">
                <i class="bi bi-x-octagon"></i> Cancelled on {{ optional($ret->cancelled_at)->format('Y-m-d H:i') }}.
                @if($ret->cancellation_reason) Reason: {{ $ret->cancellation_reason }} @endif
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Sales order</small>
                    @if($ret->salesOrder)
                        <a href="{{ route('sales.salesorders.show', $ret->salesOrder) }}">{{ $ret->salesOrder->order_number }}</a>
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-4"><small class="text-muted d-block">Customer</small>{{ $ret->customer->name ?? '—' }}</div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Invoice</small>
                    @if($ret->invoice)
                        <a href="{{ route('sales.salesinvoices.show', $ret->invoice) }}">{{ $ret->invoice->invoice_number }}</a>
                    @else
                        —
                    @endif
                </div>

                <div class="col-md-3"><small class="text-muted d-block">Return date</small>{{ optional($ret->date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Warehouse</small>{{ $ret->warehouse->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Customer reference</small>{{ $ret->reference ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Refund method</small>{{ $ret->refund_method ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Reason</small>{{ $reasons[$ret->reason_type] ?? ($ret->reason_type ?? '—') }}</div>
                <div class="col-md-9"><small class="text-muted d-block">Reason details</small>{!! nl2br(e($ret->reason ?: '—')) !!}</div>

                <div class="col-md-3"><small class="text-muted d-block">Currency</small>{{ $ret->currency }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Created by</small>{{ $ret->createdBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Validated at</small>{{ optional($ret->validated_at)->format('Y-m-d H:i') ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Cancelled at</small>{{ optional($ret->cancelled_at)->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Returned lines</div>
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
                                <th>Condition</th>
                                <th class="text-center">Restock</th>
                                <th class="text-end">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ret->lines as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? '—' }}</td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ $qty($line->quantity) }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->discount_percent, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->tax_rate, 2) }}</td>
                                    <td>{{ $conditions[$line->condition] ?? ($line->condition ?? '—') }}</td>
                                    <td class="text-center">
                                        @if($line->restock)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
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
                        <p class="text-muted">{{ $ret->notes ?: '—' }}</p>
                        <h6 class="fw-semibold">Internal notes</h6>
                        <p class="text-muted mb-0">{{ $ret->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format($ret->subtotal, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2 text-muted"><span>Discounts included</span><span>{{ number_format($ret->discount_total, 2) }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><strong>{{ number_format($ret->tax, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span><strong>{{ number_format($ret->total, 2) }} {{ $ret->currency }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock movements --}}
        @if($ret->stockMovements->isNotEmpty())
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-semibold">Stock movements</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th class="text-end">Qty</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ret->stockMovements as $movement)
                                    @php $type = strtolower((string) ($movement->type ?? $movement->movement_type ?? '')); @endphp
                                    <tr>
                                        <td>{{ optional($movement->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ $movement->product->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $type === 'in' ? 'success' : ($type === 'out' ? 'danger' : 'secondary') }}">
                                                {{ strtoupper($type ?: '—') }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ $qty($movement->quantity) }}</td>
                                        <td>{{ $movement->description ?? $movement->notes ?? $movement->note ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Cancel --}}
        @if(in_array($ret->status, ['draft', 'validated']))
            <div class="card shadow-sm mb-5 d-print-none">
                <div class="card-header fw-semibold">Cancel this return</div>
                <div class="card-body">
                    @if($ret->status === 'validated')
                        <div class="alert alert-warning py-2">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Cancelling a validated return <strong>takes the restocked goods back out</strong> of the
                            warehouse (reverse stock movements are recorded).
                        </div>
                    @endif
                    <form method="POST" action="{{ route('sales.salesreturns.cancel', $ret) }}"
                        class="row g-2 align-items-end" onsubmit="return confirm('Cancel this return?');">
                        @csrf
                        <div class="col-md-9">
                            <label class="form-label mb-1">Cancellation reason</label>
                            <input type="text" name="cancellation_reason" maxlength="255" class="form-control"
                                value="{{ old('cancellation_reason') }}" placeholder="e.g. Created by mistake, goods never received">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Cancel return
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
