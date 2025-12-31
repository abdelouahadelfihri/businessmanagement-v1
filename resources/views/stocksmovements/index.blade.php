@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Liste of Suppliers</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('suppliers.create') }}">
                <i class="bi bi-plus-lg"></i> Add a Supplier
            </a>
        </div>

        @if($suppliers->isEmpty())
            <div class="alert alert-info">No suppliers found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="suppliersTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0">
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
                                        <th scope="row">{{ $s->id }}</th>
                                        <td>{{ $s->name }}</td>
                                        <td>{{ $s->email }}</td>
                                        <td>{{ $s->phone }}</td>
                                        <td>
                                            <span
                                                class="badge @if($req->status === 'draft') bg-secondary                                                                                                                                                                                                                        @elseif($req->status === 'pending') bg-warning text-dark
                                                @elseif($req->status === 'approved') bg-success
                                                                                                                                                                                                                                    @else bg-light text-dark @endif">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <!-- Edit button -->
                                                <a href="{{ route('purchasesrequests.edit', $req) }}" class="btn btn-sm btn-warning"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>

                                                <!-- Delete button -->
                                                <form action="{{ route('purchasesrequests.destroy', $req) }}" method="POST"
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