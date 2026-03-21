@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">List of Payments</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('payments.create') }}">
                <i class="bi bi-plus-lg"></i> Add a Payment
            </a>
        </div>

        @if($payments->isEmpty())
            <div class="alert alert-info">No payments found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="paymentsTable" class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th class="text-center" style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <th>{{ $payment->id }}</th>

                                        <!-- Type -->
                                        <td>
                                            @if($payment->payable_type == 'App\Models\SalesInvoice')
                                                <span class="badge bg-success">Sales</span>
                                            @else
                                                <span class="badge bg-info">Purchase</span>
                                            @endif
                                        </td>

                                        <!-- Invoice -->
                                        <td>
                                            @if($payment->payable)
                                                #{{ $payment->payable->invoice_number ?? $payment->payable_id }}
                                            @else
                                                {{ $payment->payable_id }}
                                            @endif
                                        </td>

                                        <!-- Date -->
                                        <td>{{ $payment->payment_date }}</td>

                                        <!-- Amount -->
                                        <td>{{ number_format($payment->amount, 2) }}</td>

                                        <!-- Method -->
                                        <td>{{ $payment->payment_method }}</td>

                                        <!-- Actions -->
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">

                                                <!-- Edit -->
                                                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-sm btn-warning"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>

                                                <!-- Delete -->
                                                <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this payment?');">
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

            <!-- Bootstrap Icons -->
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
            $('#paymentsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
@endpush