@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Categories</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('categories.create') }}">
                <i class="bi bi-plus-lg"></i> Add a Category
            </a>
        </div>

        @if($categories->isEmpty())
            <div class="alert alert-info">No categories found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="locationsTable" class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th class="text-center" style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <th>{{ $category->id }}</th>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <!-- Edit button -->
                                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-warning"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>

                                                <!-- Delete button -->
                                                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Include Bootstrap Icons CDN if not already in your layout -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#locationsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush