@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Purchase Orders</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('purchases.purchasesorders.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Purchase Order
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
                <form method="GET" action="{{ route('purchases.purchasesorders.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="PO number or supplier reference..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(['draft', 'pending_approval', 'approved', 'sent', 'partially_received', 'received', 'closed', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="payment_status" class="form-label mb-1">Payment</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All</option>
                            @foreach(['unpaid', 'partial', 'paid'] as $ps)
                                <option value="{{ $ps }}" @selected(request('payment_status') === $ps)>{{ ucfirst($ps) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('purchases.purchasesorders.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($purchaseOrders->isEmpty())
            <div class="alert alert-info">No purchase orders found.</div>
        @else
            @php
                $statusColors = [
                    'draft' => 'secondary',
                    'pending_approval' => 'warning',
                    'approved' => 'success',
                    'sent' => 'info',
                    'partially_received' => 'primary',
                    'received' => 'dark',
                    'closed' => 'dark',
                    'cancelled' => 'danger',
                ];
                $paymentColors = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'];
            @endphp

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="purchaseOrdersTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>PO-Number</th>
                                    <th>Supplier</th>
                                    <th>Order date</th>
                                    <th>Expected delivery</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 200px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->po_number }}</td>
                                        <td>{{ $order->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($order->order_date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($order->expected_delivery_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td>{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</td>
                                        <td>
                                            <span class="badge bg-{{ $paymentColors[$order->payment_status] ?? 'secondary' }}">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('purchases.purchasesorders.show', $order) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($order->status === 'draft')
                                                    <a href="{{ route('purchases.purchasesorders.edit', $order) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('purchases.purchasesorders.submit', $order) }}" method="POST"
                                                        onsubmit="return confirm('Submit this order for approval?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary" title="Submit for approval">
                                                            <i class="bi bi-send"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('purchases.purchasesorders.destroy', $order) }}" method="POST"
                                                        onsubmit="return confirm('Delete this purchase order?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>

                                                @elseif($order->status === 'pending_approval')
                                                    <a href="{{ route('purchases.purchasesorders.edit', $order) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('purchases.purchasesorders.approve', $order) }}" method="POST"
                                                        onsubmit="return confirm('Approve this purchase order?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>

                                                @elseif($order->status === 'approved')
                                                    <form action="{{ route('purchases.purchasesorders.send', $order) }}" method="POST"
                                                        onsubmit="return confirm('Mark this order as sent to the supplier?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary" title="Mark as sent">
                                                            <i class="bi bi-send-check"></i>
                                                        </button>
                                                    </form>

                                                @elseif($order->status === 'cancelled')
                                                    <form action="{{ route('purchases.purchasesorders.destroy', $order) }}" method="POST"
                                                        onsubmit="return confirm('Delete this cancelled order?');">
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
                        Showing {{ $purchaseOrders->firstItem() }} to {{ $purchaseOrders->lastItem() }}
                        of {{ $purchaseOrders->total() }} orders
                    </small>
                    {{ $purchaseOrders->links() }}
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
            $('#purchaseOrdersTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush