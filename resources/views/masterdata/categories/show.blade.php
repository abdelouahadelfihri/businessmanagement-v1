@extends('layouts.app')

@section('title', $category->name)

@section('content')
    @php
        $c = $category;
        $money = fn($v) => number_format((float) $v, 2);
        $qty = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
        $canDelete = $c->children->isEmpty() && $products->total() === 0;
    @endphp

    <div class="container mt-4">
        @if($c->parent)
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item">@canroute('masterdata.categories.index')<a href="{{ route('masterdata.categories.index') }}">Categories</a>@else Categories @endcanroute</li>
                    @foreach(array_slice(explode(' › ', $c->full_name), 0, -1) as $ancestor)
                        <li class="breadcrumb-item">{{ $ancestor }}</li>
                    @endforeach
                    <li class="breadcrumb-item active" aria-current="page">{{ $c->name }}</li>
                </ol>
            </nav>
        @endif

        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0 d-flex align-items-center gap-3">
                <span class="cat-icon-lg" style="background: {{ $c->color }};"><i class="bi {{ $c->icon }}"></i></span>
                <span>
                    {{ $c->name }}
                    <span class="badge bg-{{ $c->is_active ? 'success' : 'secondary' }} fs-6 align-middle">
                        {{ $c->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <small class="d-block fs-6 text-muted mt-1">{{ $c->code }}</small>
                </span>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @canroute('masterdata.categories.create')
                <a href="{{ route('masterdata.categories.create', ['parent_id' => $c->id]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-diagram-3"></i> Add sub-category
                </a>
                @endcanroute
                @canroute('masterdata.categories.edit')
                <a href="{{ route('masterdata.categories.edit', $c) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                @endcanroute
                @if($canDelete)
                    @canroute('masterdata.categories.destroy')
                    <form action="{{ route('masterdata.categories.destroy', $c) }}" method="POST"
                        onsubmit="return confirm('Delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                    @endcanroute
                @endif
                @canroute('masterdata.categories.index')
                <a href="{{ route('masterdata.categories.index') }}" class="btn btn-outline-secondary">Back</a>
                @endcanroute
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Key figures (including sub-categories) --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Products', (int) $stats->total, 'bi-box-seam', 'primary'],
                ['Active products', (int) $stats->active, 'bi-check2-circle', 'success'],
                ['Stock value (cost)', $money($stats->stock_value), 'bi-boxes', 'warning'],
                ['Average sale price', $stats->avg_price ? $money($stats->avg_price) : '—', 'bi-tag', 'info'],
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
        @if($c->children->isNotEmpty())
            <p class="text-muted small mt-n2">Figures include all sub-categories.</p>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">General information</div>
                    <div class="card-body row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Parent category</small>
                            @if($c->parent)
                                @canroute('masterdata.categories.show')
                                <a href="{{ route('masterdata.categories.show', $c->parent) }}">{{ $c->parent->full_name }}</a>
                                @else
                                {{ $c->parent->full_name }}
                                @endcanroute
                            @else
                                Top level
                            @endif
                        </div>
                        <div class="col-sm-3"><small class="text-muted d-block">Default VAT</small>{{ $c->default_tax_rate !== null ? (float) $c->default_tax_rate . ' %' : '—' }}</div>
                        <div class="col-sm-3"><small class="text-muted d-block">Display order</small>{{ $c->sort_order }}</div>
                        <div class="col-12"><small class="text-muted d-block">Description</small>{!! nl2br(e($c->description ?: '—')) !!}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                        <span>Sub-categories</span>
                        <span class="badge bg-light text-dark">{{ $c->children->count() }}</span>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse($c->children as $child)
                            @canroute('masterdata.categories.show')
                            <a href="{{ route('masterdata.categories.show', $child) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                <span class="cat-icon" style="background: {{ $child->color }};"><i class="bi {{ $child->icon }}"></i></span>
                                <span class="flex-grow-1">{{ $child->name }}</span>
                                <span class="badge bg-light text-dark">{{ $child->products_count }} products</span>
                            </a>
                            @endcanroute
                        @empty
                            <div class="list-group-item text-muted small">No sub-categories.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Products in this category --}}
        <div class="card shadow-sm mb-5">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span>Products in this category</span>
                @canroute('masterdata.products.create')
                <a href="{{ route('masterdata.products.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg"></i> New product
                </a>
                @endcanroute
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 small text-nowrap">
                        <thead class="table-light">
                            <tr><th>Code</th><th>Name</th><th class="text-end">Sale (HT)</th><th class="text-end">Stock</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php $state = $product->stockStatus((float) $product->stock); @endphp
                                <tr>
                                    <td>{{ $product->code }}</td>
                                    <td>@canroute('masterdata.products.show')<a href="{{ route('masterdata.products.show', $product) }}">{{ $product->name }}</a>@else {{ $product->name }}@endcanroute</td>
                                    <td class="text-end">{{ $money($product->sale_price) }}</td>
                                    <td class="text-end {{ in_array($state, ['low', 'out']) ? 'text-danger fw-semibold' : '' }}">
                                        {{ $product->track_stock ? $qty($product->stock) . ' ' . ($product->unit_label ?? '') : '—' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No products in this category yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($products->hasPages())
                <div class="card-footer d-flex justify-content-center">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .cat-icon { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
        .cat-icon-lg { width: 54px; height: 54px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 1.6rem; flex-shrink: 0; }
    </style>
@endpush
