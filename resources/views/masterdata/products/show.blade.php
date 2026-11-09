@extends('layouts.app')

@section('title', $product->name)

@section('content')
    @php
        $p = $product;
        $money = fn($v) => number_format((float) $v, 2);
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
        $state = $p->stockStatus($stock);
        $stockTone = ['ok' => 'success', 'low' => 'warning', 'out' => 'danger', 'none' => 'secondary'][$state];
        $stockText = ['ok' => 'In stock', 'low' => 'Low stock', 'out' => 'Out of stock', 'none' => 'Not tracked'][$state];
        $moveColors = ['in' => 'success', 'out' => 'danger', 'transfer_in' => 'info', 'transfer_out' => 'warning', 'adjustment' => 'secondary'];
        $margin = $p->margin_percent;
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0">
                {{ $p->name }}
                <span class="badge bg-{{ $p->is_active ? 'success' : 'secondary' }} fs-6 align-middle">
                    {{ $p->is_active ? 'Active' : 'Inactive' }}
                </span>
                <small class="d-block fs-6 text-muted mt-1">
                    {{ $p->code }} · {{ \App\Models\MasterData\Product::TYPES[$p->type] ?? '' }}{{ $p->brand ? ' · ' . $p->brand : '' }}
                </small>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @canroute('masterdata.products.edit')
                <a href="{{ route('masterdata.products.edit', $p) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                @endcanroute
                @canroute('masterdata.products.destroy')
                <form action="{{ route('masterdata.products.destroy', $p) }}" method="POST"
                    onsubmit="return confirm('Delete this product?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                </form>
                @endcanroute
                @canroute('masterdata.products.index')
                <a href="{{ route('masterdata.products.index') }}" class="btn btn-outline-secondary">Back</a>
                @endcanroute
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(in_array($state, ['low', 'out']))
            <div class="alert alert-{{ $state === 'out' ? 'danger' : 'warning' }}">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ $state === 'out' ? 'This product is out of stock.' : "Stock ({$qty($stock)}) is at or below the reorder level ({$p->reorder_level})." }}
                @if($p->preferredSupplier)
                    Preferred supplier:
                    @canroute('masterdata.suppliers.show')
                    <a href="{{ route('masterdata.suppliers.show', $p->preferredSupplier) }}">{{ $p->preferredSupplier->name }}</a>@else {{ $p->preferredSupplier->name }}@endcanroute.
                @endif
            </div>
        @endif

        {{-- Key figures --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Sale price (HT)', $money($p->sale_price), 'bi-tag', 'primary'],
                ['Sale price (TTC)', $money($p->sale_price_ttc), 'bi-receipt', 'primary'],
                ['Margin', $margin === null ? '—' : $money((float) $p->sale_price - (float) $p->purchase_price) . ' (' . $margin . ' %)', 'bi-graph-up-arrow', $margin === null ? 'secondary' : ($margin < 0 ? 'danger' : 'success')],
                ['Stock', $p->track_stock ? $qty($stock) . ' ' . ($p->unit_label ?? '') : '—', 'bi-boxes', $stockTone],
            ] as [$title, $value, $icon, $tone])
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1"><i class="bi {{ $icon }} text-{{ $tone }}"></i> {{ $title }}</small>
                            <div class="fs-5 fw-bold text-break">{{ $value }}</div>
                            @if($title === 'Stock')
                                <span class="badge bg-{{ $stockTone }}">{{ $stockText }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-sm-6 col-md-3"><small class="text-muted d-block">Category</small>{{ $p->category->name ?? '—' }}</div>
                <div class="col-sm-6 col-md-3"><small class="text-muted d-block">Unit</small>{{ $p->unit ? $p->unit->name . ($p->unit->abbreviation ? ' (' . $p->unit->abbreviation . ')' : '') : '—' }}</div>
                <div class="col-sm-6 col-md-3"><small class="text-muted d-block">Barcode</small><span class="font-monospace">{{ $p->bar_code ?? '—' }}</span></div>
                <div class="col-sm-6 col-md-3"><small class="text-muted d-block">Weight</small>{{ $p->weight !== null ? $qty($p->weight) . ' kg' : '—' }}</div>
                <div class="col-12"><small class="text-muted d-block">Description</small>{!! nl2br(e($p->description ?: '—')) !!}</div>
                @if($p->bar_code)
                    <div class="col-12 text-center text-md-start">
                        <svg id="barcodeSvg" class="mw-100"></svg>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Pricing</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">Purchase price (HT)</small>{{ $money($p->purchase_price) }}</div>
                        <div class="col-6"><small class="text-muted d-block">Sale price (HT)</small>{{ $money($p->sale_price) }}</div>
                        <div class="col-6"><small class="text-muted d-block">VAT rate</small>{{ (float) $p->tax_rate }} %</div>
                        <div class="col-6"><small class="text-muted d-block">Sale price (TTC)</small>{{ $money($p->sale_price_ttc) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Stock &amp; purchasing</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">Stock tracked</small>{{ $p->track_stock ? 'Yes' : 'No' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Reorder level</small>{{ $p->track_stock ? $p->reorder_level : '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Maximum stock</small>{{ $p->track_stock && $p->max_stock !== null ? $p->max_stock : '—' }}</div>
                        <div class="col-6">
                            <small class="text-muted d-block">Preferred supplier</small>
                            @if($p->preferredSupplier)
                                @canroute('masterdata.suppliers.show')
                                <a href="{{ route('masterdata.suppliers.show', $p->preferredSupplier) }}">{{ $p->preferredSupplier->name }}</a>
                                @else
                                {{ $p->preferredSupplier->name }}
                                @endcanroute
                            @else — @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($p->track_stock)
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-semibold">Stock by warehouse &amp; recent movements</div>
                <div class="card-body row g-3">
                    <div class="col-xl-4">
                        <h6 class="fw-semibold">By warehouse</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 small text-nowrap">
                                <thead class="table-light"><tr><th>Warehouse</th><th class="text-end">Quantity</th></tr></thead>
                                <tbody>
                                    @forelse($p->warehouseStocks as $ws)
                                        <tr>
                                            <td>{{ $ws->warehouse->name ?? '—' }}</td>
                                            <td class="text-end">{{ $qty($ws->quantity) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center text-muted">No stock recorded.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-xl-8">
                        <h6 class="fw-semibold">Recent movements</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 small text-nowrap">
                                <thead class="table-light">
                                    <tr><th>Date</th><th>Type</th><th>Warehouse</th><th class="text-end">Quantity</th><th>Reason</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($movements as $m)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Carbon::parse($m->date)->format('Y-m-d') }}</td>
                                            <td><span class="badge bg-{{ $moveColors[$m->type] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $m->type)) }}</span></td>
                                            <td>
                                                {{ $m->warehouse->name ?? '—' }}
                                                @if($m->sourceWarehouse)
                                                    <small class="text-muted">from {{ $m->sourceWarehouse->name }}</small>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $qty($m->quantity) }}</td>
                                            <td>{{ $m->reason ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No stock movements yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <h6 class="fw-semibold">Notes</h6>
                <p class="text-muted mb-0">{!! nl2br(e($p->notes ?: '—')) !!}</p>
            </div>
        </div>
    </div>
@endsection

@if($product->bar_code)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
        <script>
            try {
                JsBarcode('#barcodeSvg', @json($product->bar_code), { height: 50, fontSize: 14, margin: 0 });
            } catch (e) {
                document.getElementById('barcodeSvg').remove();
            }
        </script>
    @endpush
@endif
