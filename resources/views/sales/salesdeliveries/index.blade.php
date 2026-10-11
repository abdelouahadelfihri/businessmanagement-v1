@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Sales Deliveries</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('sales.salesdeliveries.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Sales Delivery
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
                <form method="GET" action="{{ route('sales.salesdeliveries.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Delivery n°, order n°, customer or tracking..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
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
                            @foreach(\App\Models\Sales\SaleDelivery::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('sales.salesdeliveries.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($saleDeliveries->isEmpty())
            <div class="alert alert-info">No sales deliveries found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="saleDeliveriesTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Delivery n°</th>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Warehouse</th>
                                    <th>Carrier</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 170px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleDeliveries as $delivery)
                                    <tr>
                                        <td>{{ $delivery->id }}</td>
                                        <td>{{ $delivery->delivery_number }}</td>
                                        <td>
                                            @if($delivery->salesOrder)
                                                <a href="{{ route('sales.salesorders.show', $delivery->salesOrder) }}">{{ $delivery->salesOrder->order_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $delivery->salesOrder->customer->name ?? '—' }}</td>
                                        <td>{{ optional($delivery->date)->format('Y-m-d') }}</td>
                                        <td>{{ $delivery->warehouse->name ?? '—' }}</td>
                                        <td>{{ $delivery->carrier ?? '—' }}</td>
                                        <td>{{ number_format($delivery->total, 2) }} {{ $delivery->currency }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ \App\Models\Sales\SaleDelivery::STATUS_COLORS[$delivery->status] ?? 'secondary' }}">
                                                {{ ucfirst($delivery->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('sales.salesdeliveries.show', $delivery) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($delivery->status === 'draft')
                                                    <a href="{{ route('sales.salesdeliveries.edit', $delivery) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('sales.salesdeliveries.validate', $delivery) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Validate this delivery? The stock will be updated.');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Validate">
                                                            <i class="bi bi-check2-circle"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('sales.salesdeliveries.destroy', $delivery) }}"
                                                        method="POST" onsubmit="return confirm('Delete this delivery?');">
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
                        Showing {{ $saleDeliveries->firstItem() }} to {{ $saleDeliveries->lastItem() }}
                        of {{ $saleDeliveries->total() }} deliveries
                    </small>
                    {{ $saleDeliveries->links() }}
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
            $('#saleDeliveriesTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
