@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Purchase Orders</h1>

        <div class="card shadow-sm">
            <div class="card-body p-0">

                <table id="warehousesStocksTable" class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Warehouse</th>
                            <th>Product</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{ $stock->warehouse->name }}</td>
                                <td>{{ $stock->product->name }}</td>
                                <td>{{ $stock->quantity }}</td>
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
            $('#warehousesStocksTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush