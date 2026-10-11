@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Sales Orders</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('sales.salesorders.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Sales Order
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
                <form method="GET" action="{{ route('sales.salesorders.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Order n°, customer or customer PO reference..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(\App\Models\Sales\SaleOrder::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('sales.salesorders.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($saleOrders->isEmpty())
            <div class="alert alert-info">No sales orders found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="saleOrdersTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Order n°</th>
                                    <th>Customer</th>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Delivery date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 170px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleOrders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->customer->name ?? '—' }}</td>
                                        <td>{{ $order->reference ?? '—' }}</td>
                                        <td>{{ optional($order->date)->format('Y-m-d') }}</td>
                                        <td class="{{ $order->is_late ? 'text-danger fw-semibold' : '' }}">
                                            {{ optional($order->expected_delivery_date)->format('Y-m-d') ?? '—' }}
                                            @if($order->is_late)
                                                <i class="bi bi-exclamation-triangle-fill" title="Late"></i>
                                            @endif
                                        </td>
                                        <td>{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ \App\Models\Sales\SaleOrder::STATUS_COLORS[$order->status] ?? 'secondary' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('sales.salesorders.show', $order) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($order->status === 'draft')
                                                    <a href="{{ route('sales.salesorders.edit', $order) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('sales.salesorders.confirm', $order) }}" method="POST"
                                                        onsubmit="return confirm('Confirm this order?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Confirm">
                                                            <i class="bi bi-check2-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(in_array($order->status, ['draft', 'cancelled']))
                                                    <form action="{{ route('sales.salesorders.destroy', $order) }}" method="POST"
                                                        onsubmit="return confirm('Delete this order?');">
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
                        Showing {{ $saleOrders->firstItem() }} to {{ $saleOrders->lastItem() }}
                        of {{ $saleOrders->total() }} orders
                    </small>
                    {{ $saleOrders->links() }}
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
            $('#saleOrdersTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
