@extends('layouts.app')

@section('content')
    @php
        $statusColors = [
            'draft' => 'secondary', 'pending_approval' => 'warning', 'approved' => 'success',
            'sent' => 'info', 'partially_received' => 'primary', 'received' => 'dark',
            'closed' => 'dark', 'cancelled' => 'danger',
        ];
        $paymentColors = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'];
        $canCancel = !in_array($purchaseOrder->status, ['received', 'closed', 'cancelled']);
    @endphp

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h1 class="mb-0">
                {{ $purchaseOrder->po_number }}
                <span class="badge bg-{{ $statusColors[$purchaseOrder->status] ?? 'secondary' }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                </span>
                <span class="badge bg-{{ $paymentColors[$purchaseOrder->payment_status] ?? 'secondary' }} fs-6">
                    {{ ucfirst($purchaseOrder->payment_status) }}
                </span>
            </h1>

            <div class="d-flex gap-2">
                <a href="{{ route('purchases.purchasesorders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>

                @if($purchaseOrder->isEditable())
                    <a href="{{ route('purchases.purchasesorders.edit', $purchaseOrder) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                @endif

                @if($purchaseOrder->status === 'draft')
                    <form action="{{ route('purchases.purchasesorders.submit', $purchaseOrder) }}" method="POST">
                        @csrf
                        <button class="btn btn-primary"><i class="bi bi-send"></i> Submit for approval</button>
                    </form>
                @elseif($purchaseOrder->status === 'pending_approval')
                    <form action="{{ route('purchases.purchasesorders.approve', $purchaseOrder) }}" method="POST">
                        @csrf
                        <button class="btn btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                    </form>
                @elseif($purchaseOrder->status === 'approved')
                    <form action="{{ route('purchases.purchasesorders.send', $purchaseOrder) }}" method="POST">
                        @csrf
                        <button class="btn btn-primary"><i class="bi bi-send-check"></i> Mark as sent</button>
                    </form>
                @endif

                @if($canCancel)
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-lg"></i> Cancel order
                    </button>
                @endif
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted d-block">Supplier</small>{{ $purchaseOrder->supplier->name ?? '—' }}</div>
                    <div class="col-md-4"><small class="text-muted d-block">Purchase request</small>{{ $purchaseOrder->request->pr_number ?? '—' }}</div>
                    <div class="col-md-4"><small class="text-muted d-block">Supplier reference</small>{{ $purchaseOrder->supplier_reference ?: '—' }}</div>

                    <div class="col-md-4"><small class="text-muted d-block">Order date</small>{{ optional($purchaseOrder->order_date)->format('Y-m-d') }}</div>
                    <div class="col-md-4"><small class="text-muted d-block">Expected delivery</small>{{ optional($purchaseOrder->expected_delivery_date)->format('Y-m-d') ?? '—' }}</div>
                    <div class="col-md-4"><small class="text-muted d-block">Received on</small>{{ optional($purchaseOrder->received_date)->format('Y-m-d') ?? '—' }}</div>

                    <div class="col-md-4"><small class="text-muted d-block">Payment terms</small>{{ $purchaseOrder->payment_terms ?: '—' }}</div>
                    <div class="col-md-4"><small class="text-muted d-block">Shipping method</small>{{ $purchaseOrder->shipping_method ?: '—' }}</div>
                    <div class="col-md-4"><small class="text-muted d-block">Currency</small>{{ $purchaseOrder->currency }}</div>

                    <div class="col-md-4"><small class="text-muted d-block">Created by</small>{{ $purchaseOrder->creator->name ?? '—' }}</div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Approved by</small>
                        {{ $purchaseOrder->approver->name ?? '—' }}
                        @if($purchaseOrder->approved_at)
                            <small class="text-muted">({{ $purchaseOrder->approved_at->format('Y-m-d H:i') }})</small>
                        @endif
                    </div>
                    <div class="col-md-4"><small class="text-muted d-block">Sent on</small>{{ optional($purchaseOrder->sent_at)->format('Y-m-d H:i') ?? '—' }}</div>

                    @if($purchaseOrder->delivery_address)
                        <div class="col-12"><small class="text-muted d-block">Delivery address</small>{!! nl2br(e($purchaseOrder->delivery_address)) !!}</div>
                    @endif
                    @if($purchaseOrder->status === 'cancelled')
                        <div class="col-12">
                            <div class="alert alert-danger mb-0">
                                <strong>Cancelled</strong>
                                @if($purchaseOrder->cancelled_at) on {{ $purchaseOrder->cancelled_at->format('Y-m-d H:i') }} @endif
                                — {{ $purchaseOrder->cancellation_reason }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Order lines</div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Received</th>
                            <th>Unit</th>
                            <th class="text-end">Unit price</th>
                            <th class="text-end">Disc. %</th>
                            <th class="text-end">VAT %</th>
                            <th class="text-end">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $line)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $line->description }}
                                    @if($line->product)
                                        <small class="text-muted d-block">{{ $line->product->name }}</small>
                                    @endif
                                </td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($line->quantity, 3), '0'), '.') }}</td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($line->received_quantity, 3), '0'), '.') }}</td>
                                <td>{{ $line->unit ?: '—' }}</td>
                                <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($line->discount_percent, 2) }}</td>
                                <td class="text-end">{{ number_format($line->tax_rate, 2) }}</td>
                                <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-md-8">
                @if($purchaseOrder->notes)
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">Notes</div>
                        <div class="card-body">{!! nl2br(e($purchaseOrder->notes)) !!}</div>
                    </div>
                @endif
                @if($purchaseOrder->terms_conditions)
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">Terms &amp; conditions</div>
                        <div class="card-body">{!! nl2br(e($purchaseOrder->terms_conditions)) !!}</div>
                    </div>
                @endif
                @if($purchaseOrder->internal_notes)
                    <div class="card shadow-sm border-warning">
                        <div class="card-header">Internal notes</div>
                        <div class="card-body">{!! nl2br(e($purchaseOrder->internal_notes)) !!}</div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>{{ number_format($purchaseOrder->subtotal, 2) }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Discount</span><span>- {{ number_format($purchaseOrder->discount_amount, 2) }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>VAT</span><span>{{ number_format($purchaseOrder->tax_amount, 2) }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span>{{ number_format($purchaseOrder->shipping_cost, 2) }}</span></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 fw-bold">
                            <span>Total</span><span>{{ number_format($purchaseOrder->total_amount, 2) }} {{ $purchaseOrder->currency }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2 text-muted">
                            <span>Paid</span><span>{{ number_format($purchaseOrder->paid_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted">
                            <span>Balance due</span><span>{{ number_format($purchaseOrder->balance_due, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($canCancel)
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('purchases.purchasesorders.cancel', $purchaseOrder) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Cancel {{ $purchaseOrder->po_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Cancellation reason</label>
                            <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Cancel order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush