@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Purchase Orders</h1>

        <a class="btn btn-primary mb-3" href="{{ route('purchase-orders.create') }}">
            Create Purchase Order
        </a>

        <div class="card shadow-sm">
            <div class="card-body p-0">

                <table id="unitsTable" class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Supplier</th>
                            <th>Request</th>
                            <th>Order Date</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orders as $o)
                            <tr>
                                <td>{{ $o->id }}</td>
                                <td>{{ $o->supplier->name }}</td>
                                <td>{{ $o->purchaseRequest->title }}</td>
                                <td>{{ $o->order_date }}</td>
                                <td>
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

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#unitsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush