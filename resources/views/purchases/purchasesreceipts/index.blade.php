@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Purchase Receipts</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('purchases.purchasesreceipts.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Purchase Receipt
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
                <form method="GET" action="{{ route('purchases.purchasesreceipts.index') }}"
                    class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Receipt n°, delivery note or PO number..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(\App\Models\Purchases\PurchaseReceipt::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="quality_status" class="form-label mb-1">Quality</label>
                        <select name="quality_status" id="quality_status" class="form-select">
                            <option value="">All</option>
                            @foreach(\App\Models\Purchases\PurchaseReceipt::QUALITY_STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(request('quality_status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('purchases.purchasesreceipts.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($purchaseReceipts->isEmpty())
            <div class="alert alert-info">No purchase receipts found.</div>
        @else
            @php
                $statusColors = ['draft' => 'secondary', 'validated' => 'success', 'cancelled' => 'danger'];
                $qualityColors = ['pending' => 'secondary', 'passed' => 'success', 'partial' => 'warning', 'failed' => 'danger'];
            @endphp

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="purchaseReceiptsTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Receipt n°</th>
                                    <th>Purchase order</th>
                                    <th>Supplier</th>
                                    <th>Warehouse</th>
                                    <th>Date</th>
                                    <th>Delivery note</th>
                                    <th>Total</th>
                                    <th>Quality</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 170px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseReceipts as $receipt)
                                    <tr>
                                        <td>{{ $receipt->id }}</td>
                                        <td>{{ $receipt->receipt_number }}</td>
                                        <td>{{ $receipt->purchaseOrder->po_number ?? '—' }}</td>
                                        <td>{{ $receipt->supplier->name ?? '—' }}</td>
                                        <td>{{ $receipt->warehouse->name ?? '—' }}</td>
                                        <td>{{ optional($receipt->date)->format('Y-m-d') }}</td>
                                        <td>{{ $receipt->delivery_note_number ?? '—' }}</td>
                                        <td>{{ number_format($receipt->total, 2) }} {{ $receipt->currency }}</td>
                                        <td>
                                            <span class="badge bg-{{ $qualityColors[$receipt->quality_status] ?? 'secondary' }}">
                                                {{ \App\Models\Purchases\PurchaseReceipt::QUALITY_STATUSES[$receipt->quality_status] ?? ucfirst($receipt->quality_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusColors[$receipt->status] ?? 'secondary' }}">
                                                {{ ucfirst($receipt->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('purchases.purchasesreceipts.show', $receipt) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($receipt->status === 'draft')
                                                    <a href="{{ route('purchases.purchasesreceipts.edit', $receipt) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="{{ route('purchases.purchasesreceipts.validate', $receipt) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Validate this receipt?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Validate">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(in_array($receipt->status, ['draft', 'cancelled']))
                                                    <form action="{{ route('purchases.purchasesreceipts.destroy', $receipt) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Delete this purchase receipt?');">
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
                        Showing {{ $purchaseReceipts->firstItem() }} to {{ $purchaseReceipts->lastItem() }}
                        of {{ $purchaseReceipts->total() }} receipts
                    </small>
                    {{ $purchaseReceipts->links() }}
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
            $('#purchaseReceiptsTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
