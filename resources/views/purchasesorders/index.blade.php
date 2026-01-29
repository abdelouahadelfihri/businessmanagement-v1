@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Liste of Purchases Orders</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('purchasesorders.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Purchase Order
            </a>
        </div>
        @if($requests->isEmpty())
            <div class="alert alert-info">No purchases requests found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">

                        <table class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Supplier</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->supplier->name ?? '-' }}</td>
                                        <td>{{ $order->order_date }}</td>
                                        <td>{{ ucfirst($order->status) }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">

                                                @if($order->status === 'draft')

                                                    <!-- Edit -->
                                                    <a href="{{ route('purchasesorders.edit', $order->id) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <!-- Post -->
                                                    <form action="{{ route('purchasesorders.post', $order->id) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Post Order">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Delete (only allowed in draft) -->
                                                    <form action="{{ route('purchasesorders.destroy', $order->id) }}" method="POST"
                                                        style="display:inline;" onsubmit="return confirm('Delete this order?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>

                                                @elseif($order->status === 'posted')

                                                    <!-- Cancel -->
                                                    <form action="{{ route('purchasesorders.cancel', $order->id) }}" method="POST"
                                                        style="display:inline;" onsubmit="return confirm('Cancel this order?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancel Order">
                                                            <i class="bi bi-x-circle"></i>
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
            $('#purchasesRequestsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush