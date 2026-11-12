@extends('layouts.app')

@section('title', $location->name)

@section('content')
    @php
        $l = $location;
        $meta = $l->type_meta;
        $money = fn($v) => number_format((float) $v, 2);
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ' '), '0'), '.') ?: '0';
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0 d-flex align-items-center gap-3">
                <span class="loc-icon-lg bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}"><i class="bi {{ $meta['icon'] }}"></i></span>
                <span>
                    {{ $l->name }}
                    <span class="badge bg-{{ $l->is_active ? 'success' : 'secondary' }} fs-6 align-middle">{{ $l->is_active ? 'Active' : 'Inactive' }}</span>
                    <small class="d-block fs-6 text-muted mt-1">{{ $l->code }} · {{ $meta['label'] }}{{ $l->city ? ' · ' . $l->city : '' }}</small>
                </span>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @if($l->map_url)
                    <a href="{{ $l->map_url }}" target="_blank" rel="noopener" class="btn btn-outline-success"><i class="bi bi-map"></i> Open in Maps</a>
                @endif
                @canroute('masterdata.warehouses.create')
                <a href="{{ route('masterdata.warehouses.create') }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg"></i> New warehouse</a>
                @endcanroute
                @canroute('masterdata.locations.edit')
                <a href="{{ route('masterdata.locations.edit', $l) }}" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
                @endcanroute
                @canroute('masterdata.locations.destroy')
                @if($warehouses->isEmpty())
                    <form action="{{ route('masterdata.locations.destroy', $l) }}" method="POST" onsubmit="return confirm('Delete this location?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                @endif
                @endcanroute
                <a href="{{ route('masterdata.locations.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Key figures --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Warehouses', $stats['warehouses'], 'bi-buildings', 'primary'],
                ['Active warehouses', $stats['active'], 'bi-check2-circle', 'success'],
                ['Total capacity', $stats['capacity'] > 0 ? $qty($stats['capacity']) . ' m²' : '—', 'bi-bounding-box', 'info'],
                ['Stock value (cost)', $money($stats['stock_value']), 'bi-cash-stack', 'warning'],
            ] as [$title, $value, $icon, $tone])
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1"><i class="bi {{ $icon }} text-{{ $tone }}"></i> {{ $title }}</small>
                            <div class="fs-5 fw-bold text-break">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Address</div>
                    <div class="card-body row g-3">
                        <div class="col-12">{!! nl2br(e($l->full_address ?: '—')) !!}</div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">GPS coordinates</small>
                            {{ $l->hasCoordinates() ? $l->latitude . ', ' . $l->longitude : '—' }}
                        </div>
                        <div class="col-sm-6"><small class="text-muted d-block">Created</small>{{ optional($l->created_at)->format('Y-m-d') }}</div>
                        <div class="col-12"><small class="text-muted d-block">Notes</small>{!! nl2br(e($l->notes ?: '—')) !!}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Site contact</div>
                    <div class="card-body row g-3">
                        <div class="col-12"><small class="text-muted d-block">Name</small>{{ $l->contact_name ?? '—' }}</div>
                        <div class="col-sm-6 col-lg-12">
                            <small class="text-muted d-block">Phone</small>
                            @if($l->phone)<a href="tel:{{ $l->phone }}">{{ $l->phone }}</a>@else — @endif
                        </div>
                        <div class="col-sm-6 col-lg-12">
                            <small class="text-muted d-block">Email</small>
                            @if($l->email)<a href="mailto:{{ $l->email }}" class="text-break">{{ $l->email }}</a>@else — @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Warehouses at this location --}}
        <div class="card shadow-sm mb-5">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span>Warehouses at this location</span>
                <span class="badge bg-light text-dark">{{ $warehouses->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 small text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Warehouse</th>
                                <th class="text-end d-none d-sm-table-cell">Products</th>
                                <th class="text-end">Stock value</th>
                                <th class="d-none d-md-table-cell">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouses as $w)
                                <tr>
                                    <td class="text-wrap">
                                        @canroute('masterdata.warehouses.show')
                                        <a href="{{ route('masterdata.warehouses.show', $w) }}">{{ $w->name }}</a>
                                        @else
                                        {{ $w->name }}
                                        @endcanroute
                                        @if($w->is_default)<span class="badge bg-primary ms-1">Default</span>@endif
                                        @if($w->is_refrigerated)<i class="bi bi-snow text-info ms-1" title="Refrigerated"></i>@endif
                                        <small class="d-block text-muted">{{ $w->code }} · {{ $w->type_meta['label'] }}</small>
                                    </td>
                                    <td class="text-end d-none d-sm-table-cell">{{ $w->products_count }} <small class="d-block text-muted">{{ $qty($w->total_quantity) }} units</small></td>
                                    <td class="text-end">{{ $money($values[$w->id] ?? 0) }}</td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-{{ $w->is_active ? 'success' : 'secondary' }}">{{ $w->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No warehouse at this location yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .loc-icon-lg { width: 54px; height: 54px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; }
    </style>
@endpush
