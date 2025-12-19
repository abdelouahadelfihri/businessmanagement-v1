@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Purchase Orders</h1>

        <a class="btn btn-primary mb-3" href="{{ route('purchase-orders.create') }}">
            Create Purchase Order
        </a>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table id="categoriesTable" class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Desctiption</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($categories as $ca)
                            <tr>
                                <td>{{ $ca->id }}</td>
                                <td>{{ $ca->name }}</td>
                                <td>{{ $categories->description }}</td>
                                <td>
                                    <a class="btn btn-warning btn-sm" href="{{ route('categories.edit', $o) }}">
                                        Edit
                                    </a>
                                    <a class="btn btn-warning btn-sm" href="{{ route('purchase-orders.edit', $o) }}">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>

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