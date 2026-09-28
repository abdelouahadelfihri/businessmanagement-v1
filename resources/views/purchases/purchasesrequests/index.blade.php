@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Purchase Requests</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('purchases.purchasesrequests.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Purchase Request
            </a>
        </div>

        {{-- Filter & search form (outside the @if so it stays visible when no results match) --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('purchases.purchasesrequests.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="PR number or description..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(['draft', 'pending', 'approved', 'rejected', 'ordered', 'completed'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="priority" class="form-label mb-1">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="">All priorities</option>
                            @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                                <option value="{{ $priority }}" @selected(request('priority') === $priority)>
                                    {{ ucfirst($priority) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('purchases.purchasesrequests.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($purchaseRequests->isEmpty())
            <div class="alert alert-info">No purchase requests found.</div>
        @else
            @php
                $priorityColors = [
                    'low' => 'secondary',
                    'medium' => 'primary',
                    'high' => 'warning',
                    'urgent' => 'danger',
                ];
                $statusColors = [
                    'draft' => 'secondary',
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'ordered' => 'info',
                    'completed' => 'dark',
                ];
            @endphp

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="purchaseRequestsTable"
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
                                        <td>{{ $purchaseRequest->id }}</td>
                                        <td>{{ $purchaseRequest->pr_number }}</td>
                                        <td>{{ $purchaseRequest->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($purchaseRequest->date)->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $priorityColors[$purchaseRequest->priority] ?? 'secondary' }}">
                                                {{ ucfirst($purchaseRequest->priority) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($purchaseRequest->total_amount, 2) }} {{ $purchaseRequest->currency }}</td>
                                        <td>
                                            <span class="badge bg-{{ $statusColors[$purchaseRequest->status] ?? 'secondary' }}">
                                                {{ ucfirst($purchaseRequest->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">

                                                <!-- View is always available -->
                                                <a href="{{ route('purchases.purchasesrequests.show', $purchaseRequest->id) }}"
                                                    class="btn btn-sm btn-info" title="View Request">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($purchaseRequest->status === 'draft')

                                                    <a href="{{ route('purchases.purchasesrequests.edit', $purchaseRequest->id) }}"
                                                        class="btn btn-sm btn-warning" title="Edit Request">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('purchases.purchasesrequests.destroy', $purchaseRequest->id) }}"
                                                        method="POST" style="display:inline;"
                                                        onsubmit="return confirm('Are you sure you want to delete this request?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Request">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>

                                                @elseif($purchaseRequest->status === 'pending')

                                                    <form action="{{ route('purchases.purchasesrequests.approve', $purchaseRequest->id) }}"
                                                        method="POST" style="display:inline;"
                                                        onsubmit="return confirm('Approve this purchase request?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Approve Request">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn btn-sm btn-danger" title="Reject Request"
                                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $purchaseRequest->id }}">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>

                                                    <!-- Reject modal -->
                                                    <div class="modal fade" id="rejectModal{{ $purchaseRequest->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="{{ route('purchases.purchasesrequests.reject', $purchaseRequest->id) }}"
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

                                                    <form action="{{ route('purchases.purchasesrequests.destroy', $purchaseRequest->id) }}"
                                                        method="POST" style="display:inline;"
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

                {{-- Laravel pagination (server-side) --}}
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $purchaseRequests->firstItem() }} to {{ $purchaseRequests->lastItem() }}
                        of {{ $purchaseRequests->total() }} requests
                    </small>
                    {{ $purchaseRequests->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            // Search, filtering and pagination are handled by Laravel,
            // so DataTables is only used for column sorting on the current page.
            $('#purchaseRequestsTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }] // no sorting on Action column
            });
        });
    </script>
@endpush