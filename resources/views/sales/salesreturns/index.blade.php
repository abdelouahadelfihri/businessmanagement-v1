@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Sales Returns</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('sales.salesreturns.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Sales Return
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Filter & search form --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('sales.salesreturns.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Return n°, order n°, customer, reference..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="customer_id" class="form-label mb-1">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">All customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                    {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="warehouse_id" class="form-label mb-1">Warehouse</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select">
                            <option value="">All warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>
                                    {{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(\App\Models\Sales\SaleReturn::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="reason_type" class="form-label mb-1">Reason</label>
                        <select name="reason_type" id="reason_type" class="form-select">
                            <option value="">All reasons</option>
                            @foreach(\App\Models\Sales\SaleReturn::REASONS as $key => $label)
                                <option value="{{ $key }}" @selected(request('reason_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="date_from" class="form-label mb-1">From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                            value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="date_to" class="form-label mb-1">To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                            value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" title="Filter">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('sales.salesreturns.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($saleReturns->isEmpty())
            <div class="alert alert-info">No sales returns found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="saleReturnsTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Return n°</th>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Warehouse</th>
                                    <th>Date</th>
                                    <th>Reason</th>
                                    <th class="text-end">Total</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 170px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleReturns as $return)
                                    <tr>
                                        <td>{{ $return->id }}</td>
                                        <td>{{ $return->return_number }}</td>
                                        <td>
                                            @if($return->salesOrder)
                                                <a href="{{ route('sales.salesorders.show', $return->salesOrder) }}">{{ $return->salesOrder->order_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $return->customer->name ?? '—' }}</td>
                                        <td>{{ $return->warehouse->name ?? '—' }}</td>
                                        <td>{{ optional($return->date)->format('Y-m-d') }}</td>
                                        <td>{{ \App\Models\Sales\SaleReturn::REASONS[$return->reason_type] ?? ($return->reason_type ?? '—') }}</td>
                                        <td class="text-end">{{ number_format($return->total, 2) }} {{ $return->currency }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ \App\Models\Sales\SaleReturn::STATUS_COLORS[$return->status] ?? 'secondary' }}">
                                                {{ \App\Models\Sales\SaleReturn::STATUSES[$return->status] ?? ucfirst($return->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('sales.salesreturns.show', $return) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($return->isDraft())
                                                    <a href="{{ route('sales.salesreturns.edit', $return) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('sales.salesreturns.validate', $return) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Validate this return? Restocked goods will be put back into the warehouse.');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Validate">
                                                            <i class="bi bi-check2-circle"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('sales.salesreturns.destroy', $return) }}"
                                                        method="POST" onsubmit="return confirm('Delete this draft return?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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

                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $saleReturns->firstItem() }} to {{ $saleReturns->lastItem() }}
                        of {{ $saleReturns->total() }} returns
                    </small>
                    {{ $saleReturns->links() }}
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
            $('#saleReturnsTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
