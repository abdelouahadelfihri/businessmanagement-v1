@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Purchase Invoices</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('purchases.purchasesinvoices.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Purchase Invoice
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
                <form method="GET" action="{{ route('purchases.purchasesinvoices.index') }}"
                    class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Invoice n°, supplier invoice n° or PO number..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(\App\Models\Purchases\PurchaseInvoice::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="payment_status" class="form-label mb-1">Payment</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All</option>
                            @foreach(\App\Models\Purchases\PurchaseInvoice::PAYMENT_STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('payment_status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('purchases.purchasesinvoices.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($purchaseInvoices->isEmpty())
            <div class="alert alert-info">No purchase invoices found.</div>
        @else
            @php
                $statusColors = ['draft' => 'secondary', 'validated' => 'success', 'cancelled' => 'danger'];
                $paymentColors = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'];
            @endphp

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="purchaseInvoicesTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice n°</th>
                                    <th>Supplier inv. n°</th>
                                    <th>Supplier</th>
                                    <th>Date</th>
                                    <th>Due date</th>
                                    <th>Total</th>
                                    <th>Balance due</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 170px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->id }}</td>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->supplier_invoice_number ?? '—' }}</td>
                                        <td>{{ $invoice->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($invoice->date)->format('Y-m-d') }}</td>
                                        <td class="{{ $invoice->is_overdue ? 'text-danger fw-semibold' : '' }}">
                                            {{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}
                                            @if($invoice->is_overdue)
                                                <i class="bi bi-exclamation-triangle-fill" title="Overdue"></i>
                                            @endif
                                        </td>
                                        <td>{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                                        <td>{{ number_format($invoice->balance_due, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $paymentColors[$invoice->payment_status] ?? 'secondary' }}">
                                                {{ ucfirst($invoice->payment_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('purchases.purchasesinvoices.show', $invoice) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($invoice->status === 'draft')
                                                    <a href="{{ route('purchases.purchasesinvoices.edit', $invoice) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('purchases.purchasesinvoices.validate', $invoice) }}"
                                                        method="POST" onsubmit="return confirm('Validate this invoice?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Validate">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(in_array($invoice->status, ['draft', 'cancelled']))
                                                    <form action="{{ route('purchases.purchasesinvoices.destroy', $invoice) }}"
                                                        method="POST" onsubmit="return confirm('Delete this purchase invoice?');">
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
                        Showing {{ $purchaseInvoices->firstItem() }} to {{ $purchaseInvoices->lastItem() }}
                        of {{ $purchaseInvoices->total() }} invoices
                    </small>
                    {{ $purchaseInvoices->links() }}
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
            $('#purchaseInvoicesTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
