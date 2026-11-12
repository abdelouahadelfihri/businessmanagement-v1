@extends('layouts.app')

@section('title', 'Units')

@section('content')
    @php $U = \App\Models\MasterData\Unit::class; @endphp

    <div class="container mt-4">
        <h1 class="mb-4">List of Units</h1>

        <div class="d-flex flex-wrap gap-2 mb-3">
            @canroute('masterdata.units.create')
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('masterdata.units.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Unit
            </a>
            @endcanroute
            @canroute('masterdata.units.defaults')
            <form action="{{ route('masterdata.units.defaults') }}" method="POST"
                onsubmit="return confirm('Add the standard units (piece, box, kg, g, L, mL, m, cm, hour...) that do not exist yet?');">
                @csrf
                <button type="submit" class="btn btn-outline-primary rounded-pill d-inline-flex align-items-center gap-2">
                    <i class="bi bi-magic"></i> Add standard units
                </button>
            </form>
            @endcanroute
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($isEmpty)
            <div class="alert alert-info d-flex gap-3 align-items-start">
                <i class="bi bi-lightbulb fs-4"></i>
                <div>
                    <strong>No units yet.</strong>
                    Products need a unit (piece, kg, liter...).
                    @canroute('masterdata.units.defaults')
                        Click <strong>Add standard units</strong> to create the common ones in one step, then adjust them as needed.
                    @endcanroute
                </div>
            </div>
        @else
            {{-- Type pills --}}
            <div class="d-flex flex-nowrap flex-md-wrap gap-2 mb-3 overflow-auto pb-1">
                <a href="{{ route('masterdata.units.index', request()->except('type', 'page')) }}"
                    class="btn btn-sm rounded-pill text-nowrap {{ !request('type') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
                @foreach($U::TYPES as $key => $meta)
                    <a href="{{ route('masterdata.units.index', ['type' => $key] + request()->except('type', 'page')) }}"
                        class="btn btn-sm rounded-pill text-nowrap {{ request('type') === $key ? 'btn-dark' : 'btn-outline-secondary' }}">
                        <i class="bi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                        <span class="badge bg-light text-dark ms-1">{{ $typeCounts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Filter & search form --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('masterdata.units.index') }}" class="row g-2 align-items-end">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <div class="col-md-7">
                            <label for="search" class="form-label mb-1">Search</label>
                            <input type="text" name="search" id="search" class="form-control"
                                placeholder="Name, abbreviation or description..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label mb-1">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All statuses</option>
                                <option value="active" @selected(request('status') === 'active')>Active</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                            <a href="{{ route('masterdata.units.index') }}" class="btn btn-outline-secondary" title="Reset filters">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($units->isEmpty())
                <div class="alert alert-info">No units found.</div>
            @else
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="unitsTable" class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Unit</th>
                                        <th>Type</th>
                                        <th>Conversion</th>
                                        <th class="text-end d-none d-lg-table-cell">Decimals</th>
                                        <th class="text-end d-none d-lg-table-cell">Products</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 140px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($units as $unit)
                                        <tr>
                                            <td>
                                                @canroute('masterdata.units.show')
                                                <a href="{{ route('masterdata.units.show', $unit) }}" class="fw-semibold text-decoration-none">{{ $unit->name }}</a>
                                                @else
                                                <span class="fw-semibold">{{ $unit->name }}</span>
                                                @endcanroute
                                                @if($unit->abbreviation)
                                                    <span class="badge bg-light text-dark border ms-1">{{ $unit->abbreviation }}</span>
                                                @endif
                                            </td>
                                            <td><i class="bi {{ $U::TYPES[$unit->type]['icon'] ?? 'bi-rulers' }} text-muted"></i> {{ $unit->type_label }}</td>
                                            <td>
                                                @if($unit->baseUnit)
                                                    {{ $unit->conversion_text }}
                                                @else
                                                    <span class="badge bg-primary-subtle text-primary">Base unit</span>
                                                    @if($unit->derived_units_count)
                                                        <small class="text-muted">· {{ $unit->derived_units_count }} derived</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-end d-none d-lg-table-cell">{{ $unit->decimal_places }}</td>
                                            <td class="text-end d-none d-lg-table-cell">
                                                @if($unit->products_count)
                                                    @canroute('masterdata.products.index')
                                                    <a href="{{ route('masterdata.products.index', ['unit_id' => $unit->unit_id]) }}">{{ $unit->products_count }}</a>
                                                    @else
                                                    {{ $unit->products_count }}
                                                    @endcanroute
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $unit->is_active ? 'success' : 'secondary' }}">{{ $unit->is_active ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    @canroute('masterdata.units.show')
                                                    <a href="{{ route('masterdata.units.show', $unit) }}" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                                                    @endcanroute
                                                    @canroute('masterdata.units.edit')
                                                    <a href="{{ route('masterdata.units.edit', $unit) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                    @endcanroute
                                                    @canroute('masterdata.units.destroy')
                                                    @if($unit->products_count === 0 && $unit->derived_units_count === 0)
                                                        <form action="{{ route('masterdata.units.destroy', $unit) }}" method="POST"
                                                            onsubmit="return confirm('Delete this unit?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
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
                        <small class="text-muted">Showing {{ $units->firstItem() }} to {{ $units->lastItem() }} of {{ $units->total() }} units</small>
                        {{ $units->links() }}
                    </div>
                </div>
            @endif
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
            if (!document.getElementById('unitsTable')) return;
            $('#unitsTable').DataTable({
                paging: false, searching: false, info: false, ordering: true, order: [], autoWidth: false,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
