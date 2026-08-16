@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Purchase Requests</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('purchasesrequests.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Purchase Request
            </a>
        </div>

        @if($purchaseRequests->isEmpty())
            <div class="alert alert-info">No purchase requests found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="purchasesRequestsTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>PR-Number</th>
                                    <th>Supplier</th>
                                    <th>Date</th>
                                    <th>Priority</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 220px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseRequests as $purchaseRequest)
                                    <tr>
                                        <td scope="row">{{ $purchaseRequest->id }}</td>
                                        <td scope="row">{{ $purchaseRequest->pr_number }}</td>
                                        <td>{{ $purchaseRequest->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($purchaseRequest->date)->format('Y-m-d') }}</td>
                                        <td>
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
                                        </td>
                                        <td>{{ number_format($purchaseRequest->total_amount, 2) }} {{ $purchaseRequest->currency }}</td>
                                        <td>
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
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">

                                                <!-- View is always available -->
                                                <a href="{{ route('purchasesrequests.show', $purchaseRequest->id) }}"
                                                    class="btn btn-sm btn-info" title="View Request">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($purchaseRequest->status === 'draft')

                                                    <!-- Edit button -->
                                                    <a href="{{ route('purchasesrequests.edit', $purchaseRequest->id) }}"
                                                        class="btn btn-sm btn-warning" title="Edit Request">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <!-- Delete button -->
                                                    <form action="{{ route('purchasesrequests.destroy', $purchaseRequest->id) }}" method="POST"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Are you sure you want to delete this request?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Request">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>

                                                @elseif($purchaseRequest->status === 'pending')

                                                    <!-- Approve button -->
                                                    <form action="{{ route('purchasesrequests.approve', $purchaseRequest->id) }}" method="POST"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Approve this purchase request?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Approve Request">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Reject button (opens modal to capture reason) -->
                                                    <button type="button" class="btn btn-sm btn-danger" title="Reject Request"
                                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $purchaseRequest->id }}">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>

                                                    <!-- Reject modal -->
                                                    <div class="modal fade" id="rejectModal{{ $purchaseRequest->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="{{ route('purchasesrequests.reject', $purchaseRequest->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Reject {{ $purchaseRequest->pr_number }}</h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <label class="form-label">Rejection Reason</label>
                                                                        <textarea name="rejection_reason" class="form-control" rows="3"
                                                                            required></textarea>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-outline-secondary"
                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                @elseif($purchaseRequest->status === 'rejected')

                                                    <!-- Allow delete only -->
                                                    <form action="{{ route('purchasesrequests.destroy', $purchaseRequest->id) }}" method="POST"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Are you sure you want to delete this rejected request?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Request">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>

                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Include Bootstrap Icons CDN if not already in your layout -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#purchasesRequestsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush