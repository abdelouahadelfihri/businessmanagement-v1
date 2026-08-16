@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Purchase Request Details</h1>
            <a href="{{ route('purchasesrequests.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <div class="alert alert-info">
                    <strong>PR ID:</strong> {{ $purchaseRequest->id }} <br>
                    <strong>PR Number:</strong> {{ $purchaseRequest->pr_number }}
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Supplier</label>
                        <p class="mb-0">{{ optional($purchaseRequest->supplier)->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Requested By</label>
                        <p class="mb-0">{{ optional($purchaseRequest->requestedBy)->name ?? '—' }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <p class="mb-0">{{ $purchaseRequest->description ?? '—' }}</p>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <p class="mb-0">{{ optional($purchaseRequest->date)->format('Y-m-d') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Expected Date</label>
                        <p class="mb-0">{{ optional($purchaseRequest->expected_date)->format('Y-m-d') ?? '—' }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Priority</label>
                        <div>
                            @php
                                $priorityColors = [
                                    'low' => 'secondary',
                                    'medium' => 'primary',
                                    'high' => 'warning',
                                    'urgent' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$purchaseRequest->priority] ?? 'secondary' }}">
                                {{ ucfirst($purchaseRequest->priority) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <div>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'ordered' => 'info',
                                    'completed' => 'dark',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$purchaseRequest->status] ?? 'secondary' }}">
                                {{ ucfirst($purchaseRequest->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Total</label>
                        <p class="mb-0">{{ number_format($purchaseRequest->total_amount, 2) }} {{ $purchaseRequest->currency }}</p>
                    </div>
                </div>

                @if($purchaseRequest->notes)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <p class="mb-0">{{ $purchaseRequest->notes }}</p>
                    </div>
                @endif

                @if($purchaseRequest->attachment)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Attachment</label>
                        <p class="mb-0">
                            <a href="{{ Storage::url($purchaseRequest->attachment) }}" target="_blank">
                                <i class="bi bi-paperclip"></i> View attachment
                            </a>
                        </p>
                    </div>
                @endif

                @if($purchaseRequest->status === 'rejected' && $purchaseRequest->rejection_reason)
                    <div class="alert alert-danger">
                        <strong>Rejection Reason:</strong> {{ $purchaseRequest->rejection_reason }}
                    </div>
                @endif

                @if($purchaseRequest->approved_by)
                    <div class="alert alert-secondary">
                        <strong>Reviewed by:</strong> {{ optional($purchaseRequest->approvedBy)->name }} <br>
                        <strong>On:</strong> {{ optional($purchaseRequest->approved_at)->format('Y-m-d H:i') }}
                    </div>
                @endif

            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Line Items</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseRequest->lines as $line)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ optional($line->product)->name ?? '—' }}</td>
                                    <td>{{ $line->description ?? '—' }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td>{{ $line->quantity }}</td>
                                    <td>{{ number_format($line->unit_price, 2) }}</td>
                                    <td>{{ number_format($line->total_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No line items added.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <form action="{{ route('purchasesrequests.destroy', $purchaseRequest->id) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this request?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        </div>

    </div>
@endsection