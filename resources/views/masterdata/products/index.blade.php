@extends('layouts.app')

@section('title', 'Products')

@section('content')
    @php
        $stockBadge = ['ok' => 'success', 'low' => 'warning', 'out' => 'danger', 'none' => 'light text-muted'];
        $stockLabel = ['ok' => 'In stock', 'low' => 'Low', 'out' => 'Out of stock', 'none' => 'Not tracked'];
    @endphp

    <div class="container mt-4">
        <h1 class="mb-4">List of Products</h1>

        <div class="mb-3">
            @canroute('masterdata.products.create')
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('masterdata.products.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Product
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
                <form method="GET" action="{{ route('masterdata.products.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4 col-lg-3">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, code, barcode or brand..." value="{{ request('search') }}">
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="category_id" class="form-label mb-1">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="type" class="form-label mb-1">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All types</option>
                            @foreach(\App\Models\MasterData\Product::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="stock" class="form-label mb-1">Stock</label>
                        <select name="stock" id="stock" class="form-select">
                            <option value="">Any stock</option>
                            <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                            <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-1">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('masterdata.products.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($products->isEmpty())
            <div class="alert alert-info">No products found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="productsTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th class="text-end">Purchase</th>
                                    <th class="text-end">Sale (HT)</th>
                                    <th class="text-end">Margin</th>
                                    <th class="text-end">Stock</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    @php
                                        $stock = (float) $product->stock;
                                        $state = $product->stockStatus($stock);
                                        $margin = $product->margin_percent;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $product->code }}
                                            @if($product->bar_code)
                                                <small class="d-block text-muted font-monospace">{{ $product->bar_code }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @canroute('masterdata.products.show')
                                            <a href="{{ route('masterdata.products.show', $product) }}" class="fw-semibold text-decoration-none">
                                                {{ $product->name }}
                                            </a>
                                            @else
                                            {{ $product->name }}
                                            @endcanroute
                                            <small class="d-block text-muted">
                                                {{ \App\Models\MasterData\Product::TYPES[$product->type] ?? '' }}{{ $product->brand ? ' · ' . $product->brand : '' }}
                                            </small>
                                        </td>
                                        <td>{{ $product->category->name ?? '—' }}</td>
                                        <td class="text-end">{{ number_format((float) $product->purchase_price, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format((float) $product->sale_price, 2) }}</td>
                                        <td class="text-end {{ $margin === null ? 'text-muted' : ($margin < 0 ? 'text-danger' : 'text-success') }}"
                                            data-order="{{ $margin ?? -999 }}">
                                            {{ $margin === null ? '—' : $margin . ' %' }}
                                        </td>
                                        <td class="text-end" data-order="{{ $stock }}">
                                            @if($product->track_stock)
                                                {{ rtrim(rtrim(number_format($stock, 2, '.', ''), '0'), '.') ?: '0' }}
                                                <small class="text-muted">{{ $product->unit_label }}</small>
                                                <span class="badge bg-{{ $stockBadge[$state] }} ms-1">{{ $stockLabel[$state] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                @canroute('masterdata.products.show')
                                                <a href="{{ route('masterdata.products.show', $product) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @endcanroute
                                                @canroute('masterdata.products.edit')
                                                <a href="{{ route('masterdata.products.edit', $product) }}"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                @endcanroute
                                                @canroute('masterdata.products.destroy')
                                                <form action="{{ route('masterdata.products.destroy', $product) }}" method="POST"
                                                    onsubmit="return confirm('Delete this product?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
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
                    <small class="text-muted">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }}
                        of {{ $products->total() }} products
                    </small>
                    {{ $products->links() }}
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
            $('#productsTable').DataTable({
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
