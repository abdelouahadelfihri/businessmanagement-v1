@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Sales Invoices</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('sales.salesinvoices.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Sales Invoice
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
                <form method="GET" action="{{ route('sales.salesinvoices.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Invoice n°, order n°, customer, reference..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="customer_id" class="form-label mb-1">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">All customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                    {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(\App\Models\Sales\SaleInvoice::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                            <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
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

                    <div class="col-md-1 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" title="Filter">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('sales.salesinvoices.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($saleInvoices->isEmpty())
            <div class="alert alert-info">No sales invoices found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="saleInvoicesTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice n°</th>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Due date</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 170px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->id }}</td>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>
                                            @if($invoice->salesOrder)
                                                <a href="{{ route('sales.salesorders.show', $invoice->salesOrder) }}">{{ $invoice->salesOrder->order_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $invoice->customer->name ?? '—' }}</td>
                                        <td>{{ optional($invoice->date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="text-end">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                                        <td class="text-end">{{ number_format($invoice->amount_paid, 2) }}</td>
                                        <td class="text-end">{{ number_format($invoice->balance_due, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ \App\Models\Sales\SaleInvoice::STATUS_COLORS[$invoice->status] ?? 'secondary' }}">
                                                {{ \App\Models\Sales\SaleInvoice::STATUSES[$invoice->status] ?? ucfirst($invoice->status) }}
                                            </span>
                                            @if($invoice->isOverdue())
                                                <span class="badge bg-danger">Overdue</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('sales.salesinvoices.show', $invoice) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($invoice->status === 'draft')
                                                    <a href="{{ route('sales.salesinvoices.edit', $invoice) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('sales.salesinvoices.validate', $invoice) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Issue this invoice? It will no longer be editable.');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Issue">
                                                            <i class="bi bi-check2-circle"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('sales.salesinvoices.destroy', $invoice) }}"
                                                        method="POST" onsubmit="return confirm('Delete this draft invoice?');">
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
                        Showing {{ $saleInvoices->firstItem() }} to {{ $saleInvoices->lastItem() }}
                        of {{ $saleInvoices->total() }} invoices
                    </small>
                    {{ $saleInvoices->links() }}
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
            $('#saleInvoicesTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
