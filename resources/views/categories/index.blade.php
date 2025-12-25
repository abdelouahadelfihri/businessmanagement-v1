@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Categories</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('categories.create', ['select_for' => $selectFor, 'return_url' => $returnUrl]) }}">
                <i class="bi bi-plus-lg"></i> Add a New Category
            </a>
        </div>

        @if($suppliers->isEmpty())
            <div class="alert alert-info">No categories found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="categoriesTable" class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col" class="text-center" style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $s)
                                    <tr>
                                        <td>{{ $s->id }}</td>
                                        <td>{{ $s->name }}</td>
                                        <td>{{ $s->email }}</td>
                                        <td>{{ $s->phone }}</td>
                                        <td class="text-center">
                                            @if($selectFor && $returnUrl)
                                                <a class="btn btn-success btn-sm"
                                                    href="{{ $returnUrl }}?selected_supplier_id={{ $s->id }}&{{ http_build_query($extra) }}">
                                                    Select
                                                </a>
                                            @else
                                                <div class="d-flex justify-content-center gap-1">

                                                    <a href="{{ route('categories.edit', $s) }}" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </a>

                                                    <form action="{{ route('categories.destroy', $s) }}" method="POST"
                                                        onsubmit="return confirm('Delete this supplier?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>

                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    {{ $suppliers->withQueryString()->links() }}
                </div>
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
                $('#categoriesTable').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true
                });
            });
        </script>
    @endpush