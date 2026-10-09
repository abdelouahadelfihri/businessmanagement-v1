@extends('layouts.app')

@section('content')
    @php
        $statusColors = ['draft' => 'secondary', 'validated' => 'success', 'cancelled' => 'danger'];
        $qualityColors = ['pending' => 'secondary', 'passed' => 'success', 'partial' => 'warning', 'failed' => 'danger'];
        $r = $purchaseReceipt;
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">
                Purchase Receipt {{ $r->receipt_number }}
                <span class="badge bg-{{ $statusColors[$r->status] ?? 'secondary' }} fs-6 align-middle">{{ ucfirst($r->status) }}</span>
            </h1>

            <div class="d-flex gap-2">
                @if($r->status === 'draft')
                    <a href="{{ route('purchases.purchasesreceipts.edit', $r) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    <form action="{{ route('purchases.purchasesreceipts.validate', $r) }}" method="POST"
                        onsubmit="return confirm('Validate this receipt?');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Validate</button>
                    </form>

                    <form action="{{ route('purchases.purchasesreceipts.cancel', $r) }}" method="POST"
                        onsubmit="return confirm('Cancel this receipt?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Cancel</button>
                    </form>
                @endif

                <a href="{{ route('purchases.purchasesreceipts.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Supplier</small>{{ $r->supplier->name ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Purchase order</small>{{ $r->purchaseOrder->po_number ?? '—' }}</div>
                <div class="col-md-4"><small class="text-muted d-block">Warehouse</small>{{ $r->warehouse->name ?? '—' }}</div>

                <div class="col-md-3"><small class="text-muted d-block">Receipt date</small>{{ optional($r->date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Delivery note</small>{{ $r->delivery_note_number ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Carrier</small>{{ $r->carrier ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Tracking number</small>{{ $r->tracking_number ?? '—' }}</div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Quality</small>
                    <span class="badge bg-{{ $qualityColors[$r->quality_status] ?? 'secondary' }}">
                        {{ \App\Models\Purchases\PurchaseReceipt::QUALITY_STATUSES[$r->quality_status] ?? ucfirst($r->quality_status) }}
                    </span>
                </div>
                <div class="col-md-3"><small class="text-muted d-block">Received by</small>{{ $r->receivedBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Validated by</small>{{ $r->validatedBy->name ?? '—' }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Validated at</small>{{ optional($r->validated_at)->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Received lines</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Description</th>
                                <th class="text-end">Ordered</th>
                                <th class="text-end">Received</th>
                                <th class="text-end">Rejected</th>
                                <th>Unit</th>
                                <th class="text-end">Unit price</th>
                                <th class="text-end">Disc. %</th>
                                <th class="text-end">VAT %</th>
                                <th>Batch</th>
                                <th>Expiry</th>
                                <th class="text-end">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($r->items as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? '—' }}</td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($line->ordered_quantity, 3), '0'), '.') }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($line->received_quantity, 3), '0'), '.') }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($line->rejected_quantity, 3), '0'), '.') }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->discount_percent, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->tax_rate, 2) }}</td>
                                    <td>{{ $line->batch_number ?? '—' }}</td>
                                    <td>{{ optional($line->expiry_date)->format('Y-m-d') ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($line->total_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted">No lines.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-md-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold">Notes</h6>
                        <p class="text-muted">{{ $r->notes ?: '—' }}</p>
                        <h6 class="fw-semibold">Internal notes</h6>
                        <p class="text-muted mb-0">{{ $r->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format($r->subtotal, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong>{{ number_format($r->discount_amount, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><strong>{{ number_format($r->tax_amount, 2) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Shipping cost</span><strong>{{ number_format($r->shipping_cost, 2) }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span><strong>{{ number_format($r->total, 2) }} {{ $r->currency }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
