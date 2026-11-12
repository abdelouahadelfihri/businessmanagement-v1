@extends('layouts.app')

@section('title', $warehouse->name)

@section('content')
    @php
        $w = $warehouse;
        $meta = $w->type_meta;
        $money = fn($v) => number_format((float) $v, 2);
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ' '), '0'), '.') ?: '0';
        $moveColors = ['in' => 'success', 'out' => 'danger', 'transfer_in' => 'info', 'transfer_out' => 'warning', 'adjustment' => 'secondary'];
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0 d-flex align-items-center gap-3">
                <span class="wh-icon-lg bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}"><i class="bi {{ $meta['icon'] }}"></i></span>
                <span>
                    {{ $w->name }}
                    @if($w->is_default)<span class="badge bg-primary fs-6 align-middle">Default</span>@endif
                    <span class="badge bg-{{ $w->is_active ? 'success' : 'secondary' }} fs-6 align-middle">{{ $w->is_active ? 'Active' : 'Inactive' }}</span>
                    <small class="d-block fs-6 text-muted mt-1">
                        {{ $w->code }} · {{ $meta['label'] }}
                        @if($w->is_refrigerated) · <i class="bi bi-snow text-info"></i> {{ $w->temperature_range }} @endif
                    </small>
                </span>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @canroute('masterdata.warehouses.default')
                @if(! $w->is_default && $w->is_active)
                    <form action="{{ route('masterdata.warehouses.default', $w) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-star"></i> Make default</button>
                    </form>
                @endif
                @endcanroute
                @canroute('masterdata.warehouses.edit')
                <a href="{{ route('masterdata.warehouses.edit', $w) }}" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
                @endcanroute
                @canroute('masterdata.warehouses.destroy')
                @if($canDelete)
                    <form action="{{ route('masterdata.warehouses.destroy', $w) }}" method="POST" onsubmit="return confirm('Delete this warehouse?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                @endif
                @endcanroute
                <a href="{{ route('masterdata.warehouses.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @foreach(['success' => 'success', 'info' => 'info', 'error' => 'danger'] as $key => $tone)
            @if(session($key))
                <div class="alert alert-{{ $tone }}">{{ session($key) }}</div>
            @endif
        @endforeach

        {{-- Key figures --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Products in stock', (int) $stats->products, 'bi-box-seam', 'primary'],
                ['Total quantity', $qty($stats->quantity), 'bi-boxes', 'info'],
                ['Stock value (cost)', $money($stats->cost_value), 'bi-cash-stack', 'warning'],
                ['Stock value (sale)', $money($stats->sale_value), 'bi-tag', 'success'],
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
                    <div class="card-header fw-semibold">General information</div>
                    <div class="card-body row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Location</small>
                            {{ $w->location->name ?? '—' }}
                        </div>
                        <div class="col-sm-6"><small class="text-muted d-block">Type</small>{{ $meta['label'] }}</div>
                        <div class="col-12"><small class="text-muted d-block">Address</small>{!! nl2br(e($w->full_address ?: '—')) !!}</div>
                        <div class="col-12"><small class="text-muted d-block">Notes</small>{!! nl2br(e($w->notes ?: '—')) !!}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Manager &amp; storage</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">Manager</small>{{ $w->manager_name ?? '—' }}</div>
                        <div class="col-6">
                            <small class="text-muted d-block">Phone</small>
                            @if($w->phone)<a href="tel:{{ $w->phone }}">{{ $w->phone }}</a>@else — @endif
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Email</small>
                            @if($w->email)<a href="mailto:{{ $w->email }}" class="text-break">{{ $w->email }}</a>@else — @endif
                        </div>
                        <div class="col-6"><small class="text-muted d-block">Capacity</small>{{ $w->capacity_m2 !== null ? $qty($w->capacity_m2) . ' m²' : '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Cold storage</small>{{ $w->is_refrigerated ? $w->temperature_range : 'No' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock --}}
        <div class="card shadow-sm mb-3" id="stock">
            <div class="card-header fw-semibold d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <span>Stock in this warehouse</span>
                <form method="GET" action="{{ route('masterdata.warehouses.show', $w) }}#stock" class="d-flex gap-2">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Find a product..." value="{{ request('q') }}">
                    <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 small text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="d-none d-md-table-cell">Category</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end d-none d-sm-table-cell">Value (cost)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $line)
                                @php $p = $line->product; @endphp
                                <tr>
                                    <td>
                                        @if($p)
                                            @canroute('masterdata.products.show')
                                            <a href="{{ route('masterdata.products.show', $p) }}">{{ $p->name }}</a>
                                            @else
                                            {{ $p->name }}
                                            @endcanroute
                                            <small class="d-block text-muted">{{ $p->code }}</small>
                                        @else — @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $p?->category?->name ?? '—' }}</td>
                                    <td class="text-end {{ $line->quantity < 0 ? 'text-danger fw-semibold' : '' }}">
                                        {{ $qty($line->quantity) }} <small class="text-muted">{{ $p?->unit_label }}</small>
                                    </td>
                                    <td class="text-end d-none d-sm-table-cell">{{ $money($line->quantity * (float) ($p->purchase_price ?? 0)) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">{{ request('q') ? 'No matching product in stock.' : 'No stock in this warehouse yet.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($stocks->hasPages())
                <div class="card-footer d-flex justify-content-center">{{ $stocks->fragment('stock')->links() }}</div>
            @endif
        </div>

        {{-- Movements --}}
        <div class="card shadow-sm mb-5">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span>Recent stock movements</span>
                @canroute('masterdata.stocksmovements.index')
                <a href="{{ route('masterdata.stocksmovements.index') }}" class="small">All movements</a>
                @endcanroute
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small text-nowrap">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Type</th><th>Product</th><th class="text-end">Quantity</th><th class="d-none d-md-table-cell">Reason</th></tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $m)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($m->date)->format('Y-m-d') }}</td>
                                    <td><span class="badge bg-{{ $moveColors[$m->type] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $m->type)) }}</span></td>
                                    <td>{{ $m->product->name ?? '—' }}</td>
                                    <td class="text-end">{{ $qty($m->quantity) }}</td>
                                    <td class="d-none d-md-table-cell">{{ $m->reason ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No stock movements yet.</td></tr>
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
        .wh-icon-lg { width: 54px; height: 54px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; }
    </style>
@endpush
