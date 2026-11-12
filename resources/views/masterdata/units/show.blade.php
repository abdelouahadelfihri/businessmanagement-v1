@extends('layouts.app')

@section('title', $unit->name)

@section('content')
    @php
        $u = $unit;
        $U = \App\Models\MasterData\Unit::class;
        $canDelete = $u->products_count === 0 && $u->derivedUnits->isEmpty();
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0 d-flex align-items-center gap-3">
                <span class="unit-badge">{{ $u->short }}</span>
                <span>
                    {{ $u->name }}
                    <span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }} fs-6 align-middle">{{ $u->is_active ? 'Active' : 'Inactive' }}</span>
                    <small class="d-block fs-6 text-muted mt-1">
                        <i class="bi {{ $U::TYPES[$u->type]['icon'] ?? 'bi-rulers' }}"></i> {{ $u->type_label }}
                        · {{ $u->baseUnit ? 'Derived unit' : 'Base unit' }}
                    </small>
                </span>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @canroute('masterdata.units.create')
                <a href="{{ route('masterdata.units.create', ['base_unit_id' => $u->unit_id]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-diagram-3"></i> Add derived unit
                </a>
                @endcanroute
                @canroute('masterdata.units.edit')
                <a href="{{ route('masterdata.units.edit', $u) }}" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
                @endcanroute
                @canroute('masterdata.units.destroy')
                @if($canDelete)
                    <form action="{{ route('masterdata.units.destroy', $u) }}" method="POST" onsubmit="return confirm('Delete this unit?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                @endif
                @endcanroute
                <a href="{{ route('masterdata.units.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-3">
            @foreach([
                ['Abbreviation', $u->abbreviation ?? '—', 'bi-type', 'primary'],
                ['Decimal places', $u->decimal_places . ' (' . number_format(1, $u->decimal_places, '.', '') . ')', 'bi-123', 'info'],
                ['Products using it', $u->products_count, 'bi-box-seam', 'success'],
                ['Conversion', $u->conversion_text ?? 'Base unit', 'bi-arrow-left-right', 'warning'],
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
            {{-- Equivalences --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold"><i class="bi bi-arrow-left-right"></i> Equivalences of 1 {{ $u->short }}</div>
                    <div class="list-group list-group-flush">
                        @forelse($family as $row)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    @canroute('masterdata.units.show')
                                    <a href="{{ route('masterdata.units.show', $row['unit']) }}">{{ $row['unit']->name }}</a>
                                    @else
                                    {{ $row['unit']->name }}
                                    @endcanroute
                                    @unless($row['unit']->baseUnit)
                                        <span class="badge bg-primary-subtle text-primary ms-1">Base</span>
                                    @endunless
                                </span>
                                <strong>{{ $U::number($row['value']) }} {{ $row['unit']->short }}</strong>
                            </div>
                        @empty
                            <div class="list-group-item text-muted small">
                                No other unit of this family yet.
                                @canroute('masterdata.units.create')
                                    <a href="{{ route('masterdata.units.create', ['base_unit_id' => $u->unit_id]) }}">Add a derived unit</a>
                                    (e.g. a box of 12 pieces).
                                @endcanroute
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Details</div>
                    <div class="card-body row g-3">
                        <div class="col-sm-6"><small class="text-muted d-block">Type</small>{{ $u->type_label }}</div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Based on</small>
                            @if($u->baseUnit)
                                @canroute('masterdata.units.show')
                                <a href="{{ route('masterdata.units.show', $u->baseUnit) }}">{{ $u->baseUnit->label }}</a>
                                @else
                                {{ $u->baseUnit->label }}
                                @endcanroute
                            @else
                                — (base unit)
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Derived units</small>
                            {{ $u->derivedUnits->isEmpty() ? '—' : $u->derivedUnits->pluck('short')->implode(', ') }}
                        </div>
                        <div class="col-sm-6"><small class="text-muted d-block">Created</small>{{ optional($u->created_at)->format('Y-m-d') }}</div>
                        <div class="col-12"><small class="text-muted d-block">Description</small>{{ $u->description ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div class="card shadow-sm mb-5">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span>Products using this unit</span>
                <span class="badge bg-light text-dark">{{ $u->products_count }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 small text-nowrap">
                        <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Category</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>{{ $product->code }}</td>
                                    <td>
                                        @canroute('masterdata.products.show')
                                        <a href="{{ route('masterdata.products.show', $product) }}">{{ $product->name }}</a>
                                        @else
                                        {{ $product->name }}
                                        @endcanroute
                                    </td>
                                    <td>{{ $product->category->name ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No product uses this unit yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($u->products_count > $products->count())
                <div class="card-footer small text-muted">Showing the first {{ $products->count() }} of {{ $u->products_count }} products.</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .unit-badge { min-width: 54px; height: 54px; padding: 0 10px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center;
            background: #e7efff; color: #2f6bff; font-size: 1.1rem; font-weight: 700; flex-shrink: 0; }
    </style>
@endpush
