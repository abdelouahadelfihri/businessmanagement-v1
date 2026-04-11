@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h3 class="m-0">Stock Movements</h3>
            <div class="d-flex gap-2">
                <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                    href="{{ route('stocksmovements.create') }}">
                    <i class="bi bi-plus-lg"></i> Add Movement
                </a>

                <a class="btn btn-secondary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                    href="{{ route('stocksmovements.transfer_form') }}">
                    <i class="bi bi-arrow-left-right"></i> Transfer Stock
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($movements->isEmpty())
            <div class="alert alert-info">No stocks movements found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="suppliersTable" class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Product</th>
                                    <th scope="col">Warehouse</th>
                                    <th scope="col">Source Warehouse</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Source Doc</th>
                                    <th scope="col" class="text-center" style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $m)
                                    <tr>
                                        <td>{{ $m->id }}</td>
                                        <td>{{ $m->product->name }}</td>
                                        <td>{{ $m->warehouse->name }}</td>
                                        <td>{{ $m->sourceWarehouse ? $m->sourceWarehouse->name : '-' }}</td>
                                        <td>{{ ucfirst($m->type) }}</td>
                                        <td>{{ $m->quantity }}</td>
                                        <td>{{ $m->date }}</td>
                                        <td>{{ $m->source_type ? class_basename($m->source_type) . ' #' . $m->source_id : '-' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <!-- Edit button -->
                                                <a href="{{ route('stocksmovements.edit', $req) }}" class="btn btn-sm btn-warning"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>

                                                <!-- Delete button -->
                                                <form action="{{ route('stocksmovements.destroy', $req) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this request?');">
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

            <div class="mt-3">
                {{ $requests->withQueryString()->links() }}
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