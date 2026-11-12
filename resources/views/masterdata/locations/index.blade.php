@extends('layouts.app')

@section('title', 'Locations')

@section('content')
    @php $L = \App\Models\MasterData\Location::class; @endphp

    <div class="container mt-4">
        <h1 class="mb-4">List of Locations</h1>

        <div class="mb-3">
            @canroute('masterdata.locations.create')
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('masterdata.locations.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Location
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
                <form method="GET" action="{{ route('masterdata.locations.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, code, address, city or contact..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="type" class="form-label mb-1">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All types</option>
                            @foreach($L::TYPES as $key => $meta)
                                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $meta['label'] }}</option>
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
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('masterdata.locations.index') }}" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($locations->isEmpty())
            <div class="alert alert-info">No locations found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="locationsTable" class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Location</th>
                                    <th class="d-none d-lg-table-cell">Address</th>
                                    <th class="d-none d-xl-table-cell">Contact</th>
                                    <th class="text-end d-none d-md-table-cell">Warehouses</th>
                                    <th>Status</th>
                                    <th class="text-center d-none d-sm-table-cell" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locations as $location)
                                    @php $meta = $location->type_meta; @endphp
                                    <tr>
                                        <td class="loc-name-cell">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="loc-icon bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}"><i class="bi {{ $meta['icon'] }}"></i></span>
                                                <div>
                                                    @canroute('masterdata.locations.show')
                                                    <a href="{{ route('masterdata.locations.show', $location) }}" class="fw-semibold text-decoration-none">{{ $location->name }}</a>
                                                    @else
                                                    <span class="fw-semibold">{{ $location->name }}</span>
                                                    @endcanroute
                                                    <small class="d-block text-muted">{{ $location->code }} · {{ $meta['label'] }}{{ $location->city ? ' · ' . $location->city : '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="d-none d-lg-table-cell loc-address-cell">
                                            {{ $location->address ?? '—' }}
                                            @if($location->city)<small class="d-block text-muted">{{ trim($location->postal_code . ' ' . $location->city) }}</small>@endif
                                        </td>
                                        <td class="d-none d-xl-table-cell">
                                            {{ $location->contact_name ?? '—' }}
                                            @if($location->phone)<small class="d-block text-muted">{{ $location->phone }}</small>@endif
                                        </td>
                                        <td class="text-end d-none d-md-table-cell">{{ $location->warehouses_count }}</td>
                                        <td>
                                            <span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }}">{{ $location->is_active ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="text-center d-none d-sm-table-cell">
                                            <div class="d-flex justify-content-center gap-1">
                                                @canroute('masterdata.locations.show')
                                                <a href="{{ route('masterdata.locations.show', $location) }}" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                                                @endcanroute
                                                @canroute('masterdata.locations.edit')
                                                <a href="{{ route('masterdata.locations.edit', $location) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                @endcanroute
                                                @if($location->map_url)
                                                    <a href="{{ $location->map_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Open in Google Maps"><i class="bi bi-map"></i></a>
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
                    <small class="text-muted">Showing {{ $locations->firstItem() }} to {{ $locations->lastItem() }} of {{ $locations->total() }} locations</small>
                    {{ $locations->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        .loc-name-cell { white-space: normal; min-width: 150px; }
        .loc-address-cell { white-space: normal; min-width: 180px; max-width: 280px; }
        .loc-icon { width: 34px; height: 34px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            if (!document.getElementById('locationsTable')) return;
            $('#locationsTable').DataTable({
                paging: false, searching: false, info: false, ordering: true, order: [], autoWidth: false,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
