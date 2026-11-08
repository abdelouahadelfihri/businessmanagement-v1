@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Categories</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('masterdata.categories.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Category
            </a>
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
                <form method="GET" action="{{ route('masterdata.categories.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, code or description..." value="{{ request('search') }}">
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="parent_id" class="form-label mb-1">Parent</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">Any parent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(request('parent_id') == $parent->id)>{{ $parent->name }}</option>
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
                        <a href="{{ route('masterdata.categories.index') }}" class="btn btn-outline-secondary"
                            title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="level" value="root" id="level"
                                @checked(request('level') === 'root') onchange="this.form.submit()">
                            <label class="form-check-label" for="level">Top-level categories only</label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($categories->isEmpty())
            <div class="alert alert-info">No categories found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="categoriesTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Parent</th>
                                    <th class="text-end">Products</th>
                                    <th class="text-end">Sub-categories</th>
                                    <th>Default VAT</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td>{{ $category->code }}</td>
                                        <td>
                                            <a href="{{ route('masterdata.categories.show', $category) }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
                                                <span class="cat-icon" style="background: {{ $category->color }};"><i class="bi {{ $category->icon }}"></i></span>
                                                <span class="fw-semibold">{{ $category->name }}</span>
                                            </a>
                                            @if($category->description)
                                                <small class="d-block text-muted text-truncate" style="max-width: 260px;">{{ $category->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($category->parent)
                                                <a href="{{ route('masterdata.categories.show', $category->parent) }}">{{ $category->parent->name }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('masterdata.products.index', ['category_id' => $category->id]) }}">{{ $category->products_count }}</a>
                                        </td>
                                        <td class="text-end">{{ $category->children_count }}</td>
                                        <td>{{ $category->default_tax_rate !== null ? (float) $category->default_tax_rate . ' %' : '—' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('masterdata.categories.show', $category) }}"
                                                    class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('masterdata.categories.edit', $category) }}"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                @if($category->products_count === 0 && $category->children_count === 0)
                                                    <form action="{{ route('masterdata.categories.destroy', $category) }}" method="POST"
                                                        onsubmit="return confirm('Delete this category?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
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
                        Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }}
                        of {{ $categories->total() }} categories
                    </small>
                    {{ $categories->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        .cat-icon { width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#categoriesTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                order: [],
                autoWidth: false,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
