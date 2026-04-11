@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Customers</h1>

        <div class="mb-3">
            <a class="btn btn-primary"
                href="{{ route('customers.create', ['select_for' => $selectFor, 'return_url' => $returnUrl]) }}">
                Add Customer
            </a>
        </div>

        @if($customers->isEmpty())
            <div class="alert alert-info">No suppliers found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-bordered" id="suppliersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $s)
                                <tr>
                                    <td>{{ $s->name }}</td>
                                    <td>{{ $s->email }}</td>
                                    <td>
                                        @if($selectFor && $returnUrl)
                                            @php
                                                $form = session('sale_quotation_form', []);
                                                $query = array_merge(['selected_customer_id' => $s->id], $form);
                                            @endphp
                                            <a class="btn btn-success btn-sm" href="{{ $returnUrl }}?{{ http_build_query($query) }}">
                                                Select
                                            </a>
                                        @else
                                            <a class="btn btn-warning btn-sm" href="{{ route('customers.edit', $s) }}">Edit</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $customers->withQueryString()->links() }}
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
            $('#suppliersTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush