@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
    @php
        $W = \App\Models\MasterData\Warehouse::class;
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ' '), '0'), '.') ?: '0';
    @endphp

    <div class="container mt-4">
        <h1 class="mb-4">List of Warehouses</h1>

        <div class="mb-3">
            @canroute('masterdata.warehouses.create')
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('masterdata.warehouses.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Warehouse
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
                <form method="GET" action="{{ route('masterdata.warehouses.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, code, manager or address..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="type" class="form-label mb-1">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All types</option>
                            @foreach($W::TYPES as $key => $meta)
                                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="location_id" class="form-label mb-1">Location</label>
                        <select name="location_id" id="location_id" class="form-select">
                            <option value="">All locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->location_id }}" @selected(request('location_id') == $location->location_id)>{{ $location->name }}</option>
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
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('masterdata.warehouses.index') }}" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($warehouses->isEmpty())
            <div class="alert alert-info">
                No warehouses found.
                @if(! request()->hasAny(['search', 'type', 'location_id', 'status']))
                    Add your first warehouse — it becomes the default one for receipts and deliveries.
                @endif
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="warehousesTable" class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Warehouse</th>
                                    <th class="d-none d-lg-table-cell">Location</th>
                                    <th class="d-none d-xl-table-cell">Manager</th>
                                    <th class="text-end d-none d-md-table-cell">Products</th>
                                    <th class="text-end d-none d-lg-table-cell">Stock value</th>
                                    <th>Status</th>
                                    <th class="text-center d-none d-sm-table-cell" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehouses as $warehouse)
                                    @php $meta = $warehouse->type_meta; @endphp
                                    <tr>
                                        <td class="wh-name-cell">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="wh-icon bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}"><i class="bi {{ $meta['icon'] }}"></i></span>
                                                <div>
                                                    @canroute('masterdata.warehouses.show')
                                                    <a href="{{ route('masterdata.warehouses.show', $warehouse) }}" class="fw-semibold text-decoration-none">{{ $warehouse->name }}</a>
                                                    @else
                                                    <span class="fw-semibold">{{ $warehouse->name }}</span>
                                                    @endcanroute
                                                    @if($warehouse->is_default)
                                                        <span class="badge bg-primary ms-1">Default</span>
                                                    @endif
                                                    @if($warehouse->is_refrigerated)
                                                        <i class="bi bi-snow text-info ms-1" title="Refrigerated"></i>
                                                    @endif
                                                    <small class="d-block text-muted">{{ $warehouse->code }} · {{ $meta['label'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="d-none d-lg-table-cell">{{ $warehouse->location->name ?? '—' }}</td>
                                        <td class="d-none d-xl-table-cell">
                                            {{ $warehouse->manager_name ?? '—' }}
                                            @if($warehouse->phone)<small class="d-block text-muted">{{ $warehouse->phone }}</small>@endif
                                        </td>
                                        <td class="text-end d-none d-md-table-cell">
                                            {{ $warehouse->products_count }}
                                            <small class="d-block text-muted">{{ $qty($warehouse->total_quantity) }} units</small>
                                        </td>
                                        <td class="text-end d-none d-lg-table-cell" data-order="{{ (float) ($values[$warehouse->id] ?? 0) }}">
                                            {{ number_format((float) ($values[$warehouse->id] ?? 0), 2) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'secondary' }}">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="text-center d-none d-sm-table-cell">
                                            <div class="d-flex justify-content-center gap-1">
                                                @canroute('masterdata.warehouses.show')
                                                <a href="{{ route('masterdata.warehouses.show', $warehouse) }}" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                                                @endcanroute
                                                @canroute('masterdata.warehouses.edit')
                                                <a href="{{ route('masterdata.warehouses.edit', $warehouse) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                @endcanroute
                                                @canroute('masterdata.warehouses.default')
                                                @if(! $warehouse->is_default && $warehouse->is_active)
                                                    <form action="{{ route('masterdata.warehouses.default', $warehouse) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Make default"><i class="bi bi-star"></i></button>
                                                    </form>
                                                @endif
                                                @endcanroute
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <small class="text-muted">Showing {{ $warehouses->firstItem() }} to {{ $warehouses->lastItem() }} of {{ $warehouses->total() }} warehouses</small>
                    {{ $warehouses->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        .wh-name-cell { white-space: normal; min-width: 150px; }
        .wh-icon { width: 34px; height: 34px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            if (!document.getElementById('warehousesTable')) return;
            $('#warehousesTable').DataTable({
                paging: false, searching: false, info: false, ordering: true, order: [], autoWidth: false,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
