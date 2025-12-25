@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Suppliers</h1>

        <div class="mb-3">
            <a class="btn btn-primary"
                href="{{ route('suppliers.create', ['select_for' => $selectFor, 'return_url' => $returnUrl]) }}">
                Add Supplier
            </a>
        </div>

        @if($suppliers->isEmpty())
            <div class="alert alert-info">No suppliers found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="purchasesRequestsTable"
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
                                        <td>{{ $s->name }}</td>
                                        <td>{{ $s->email }}</td>
                                        <td>{{ $s->phone }}</td>
                                        <td>
                                            @if($selectFor && $returnUrl)
                                                @php
                                                    $form = session('purchase_request_form', []);
                                                    $query = array_merge(['selected_supplier_id' => $s->id], $form);
                                                @endphp
                                                <a class="btn btn-success btn-sm"
                                                    href="{{ $returnUrl }}?{{ http_build_query($query) }}">
                                                    Select
                                                </a>
                                            @else
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
                $('#suppliersTable').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true
                });
            });
        </script>
    @endpush