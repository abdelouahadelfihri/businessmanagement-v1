@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Purchase Orders</h1>
        <a href="{{ route('purchasesorders.create') }}" class="btn btn-primary mb-3">New Purchase Order</a>

        <table class="table table-striped table-hover table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Supplier</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-center" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->supplier->name ?? '-' }}</td>
                        <td>{{ $order->order_date }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>
                            @if($order->status === 'draft')
                                <a href="{{ route('purchasesorders.edit', $order->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('purchasesorders.post', $order->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Post</button>
                                </form>
                            @elseif($order->status === 'posted')
                                <form action="{{ route('purchasesorders.cancel', $order->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
            $('#purchasesRequestsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush