@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Suppliers</h1>

        @if($suppliers->isEmpty())
            <div class="alert alert-info">No suppliers found.</div>

            @if($selectFor && $returnUrl)
                <a class="btn btn-primary"
                    href="{{ route('suppliers.create', ['select_for' => $selectFor, 'return_url' => $returnUrl]) }}">
                    Add Supplier and Return
                </a>
            @else
                <a class="btn btn-primary" href="{{ route('suppliers.create') }}">Add Supplier</a>
            @endif
        @else

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table id="suppliersTable" class="table table-bordered table-hover"></table>
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>
                                    @if($selectFor && $returnUrl)
                                        <a class="btn btn-success btn-sm" href="{{ $returnUrl }}?selected_supplier_id={{ $s->id }}">
                                            Select
                                        </a>
                                    @else
                                        <a class="btn btn-warning btn-sm" href="{{ route('suppliers.edit', $s->id) }}">
                                            Edit
                                        </a>
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

        @if(!$selectFor)
            <a class="btn btn-primary mt-3" href="{{ route('suppliers.create') }}">Add Supplier</a>
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
            $('#suppliersTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush