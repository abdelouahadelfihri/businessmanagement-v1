@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Suppliers</h1>

        <div class="mb-3">
            @canroute('masterdata.suppliers.create')
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('masterdata.suppliers.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Supplier
            </a>
            @endcanroute
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
                <form method="GET" action="{{ route('masterdata.suppliers.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, code, contact, email, phone, city or ICE..." value="{{ request('search') }}">
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="type" class="form-label mb-1">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All types</option>
                            @foreach(\App\Models\MasterData\Supplier::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="city" class="form-label mb-1">City</label>
                        <select name="city" id="city" class="form-select">
                            <option value="">All cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('masterdata.suppliers.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($suppliers->isEmpty())
            <div class="alert alert-info">No suppliers found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="suppliersTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Primary contact</th>
                                    <th>Phone</th>
                                    <th>City</th>
                                    <th>Rating</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Amount due</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->code }}</td>
                                        <td>
                                            @canroute('masterdata.suppliers.show')
                                            <a href="{{ route('masterdata.suppliers.show', $supplier) }}" class="fw-semibold text-decoration-none">
                                                {{ $supplier->name }}
                                            </a>
                                            @else
                                            {{ $supplier->name }}
                                            @endcanroute
                                            <small class="d-block text-muted">{{ $supplier->email ?? (\App\Models\MasterData\Supplier::TYPES[$supplier->type] ?? '') }}</small>
                                        </td>
                                        <td>
                                            @if($supplier->primaryContact)
                                                @canroute('crm.contacts.show')
                                                <a href="{{ route('crm.contacts.show', $supplier->primaryContact) }}">{{ $supplier->primaryContact->full_name }}</a>
                                                @else
                                                {{ $supplier->primaryContact->full_name }}
                                                @endcanroute
                                                <small class="d-block text-muted">{{ $supplier->primaryContact->job_title }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $supplier->phone ?? $supplier->mobile ?? '—' }}</td>
                                        <td>{{ $supplier->city ?? '—' }}</td>
                                        <td class="text-warning" data-order="{{ $supplier->rating ?? 0 }}">
                                            @if($supplier->rating)
                                                {{ str_repeat('★', $supplier->rating) }}<span class="text-muted">{{ str_repeat('☆', 5 - $supplier->rating) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $supplier->orders_count }}</td>
                                        <td class="text-end {{ $supplier->amount_due > 0 ? 'fw-semibold text-danger' : 'text-muted' }}"
                                            data-order="{{ (float) $supplier->amount_due }}">
                                            {{ number_format((float) $supplier->amount_due, 2) }} {{ $supplier->currency }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">
                                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                @canroute('masterdata.suppliers.show')
                                                <a href="{{ route('masterdata.suppliers.show', $supplier) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @endcanroute
                                                @canroute('masterdata.suppliers.edit')
                                                <a href="{{ route('masterdata.suppliers.edit', $supplier) }}"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                @endcanroute
                                                @if(!($supplier->requests_count || $supplier->orders_count || $supplier->receipts_count || $supplier->invoices_count || $supplier->returns_count))
                                                    @canroute('masterdata.suppliers.destroy')
                                                    <form action="{{ route('masterdata.suppliers.destroy', $supplier) }}" method="POST"
                                                        onsubmit="return confirm('Delete this supplier?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endcanroute
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        Showing {{ $suppliers->firstItem() }} to {{ $suppliers->lastItem() }}
                        of {{ $suppliers->total() }} suppliers
                    </small>
                    {{ $suppliers->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#suppliersTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                autoWidth: false,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
