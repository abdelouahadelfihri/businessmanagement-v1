@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Customers</h1>

        <div class="mb-3">
            @canroute('masterdata.customers.create')
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('masterdata.customers.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Customer
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
                <form method="GET" action="{{ route('masterdata.customers.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, code, contact, email, phone, city or ICE..." value="{{ request('search') }}">
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="type" class="form-label mb-1">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All types</option>
                            @foreach(\App\Models\MasterData\Customer::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
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
                        <a href="{{ route('masterdata.customers.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($customers->isEmpty())
            <div class="alert alert-info">No customers found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="customersTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Primary contact</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>City</th>
                                    <th class="text-end">Outstanding</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->code }}</td>
                                        <td>
                                            @canroute('masterdata.customers.show')
                                            <a href="{{ route('masterdata.customers.show', $customer) }}" class="fw-semibold text-decoration-none">
                                                {{ $customer->name }}
                                            </a>
                                            @else
                                            {{ $customer->name }}
                                            @endcanroute
                                            <small class="d-block text-muted">{{ \App\Models\MasterData\Customer::TYPES[$customer->type] ?? '' }}</small>
                                        </td>
                                        <td>
                                            @if($customer->primaryContact)
                                                @canroute('crm.contacts.show')
                                                <a href="{{ route('crm.contacts.show', $customer->primaryContact) }}">{{ $customer->primaryContact->full_name }}</a>
                                                @else
                                                {{ $customer->primaryContact->full_name }}
                                                @endcanroute
                                                <small class="d-block text-muted">{{ $customer->primaryContact->job_title }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $customer->phone ?? $customer->mobile ?? '—' }}</td>
                                        <td>{{ $customer->email ?? '—' }}</td>
                                        <td>{{ $customer->city ?? '—' }}</td>
                                        <td class="text-end {{ $customer->outstanding > 0 ? 'fw-semibold text-danger' : 'text-muted' }}">
                                            {{ number_format((float) $customer->outstanding, 2) }} {{ $customer->currency }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                @canroute('masterdata.customers.show')
                                                <a href="{{ route('masterdata.customers.show', $customer) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @endcanroute
                                                @canroute('masterdata.customers.edit')
                                                <a href="{{ route('masterdata.customers.edit', $customer) }}"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                @endcanroute
                                                @if($customer->invoices_count === 0)
                                                    @canroute('masterdata.customers.destroy')
                                                    <form action="{{ route('masterdata.customers.destroy', $customer) }}" method="POST"
                                                        onsubmit="return confirm('Delete this customer?');">
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
                        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }}
                        of {{ $customers->total() }} customers
                    </small>
                    {{ $customers->links() }}
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
            $('#customersTable').DataTable({
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
