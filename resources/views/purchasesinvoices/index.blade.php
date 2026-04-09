@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Purchase Invoices</h1>

        {{-- Create normal invoice --}}
        <a class="btn btn-primary mb-3" href="{{ route('purchase-invoices.create') }}">
            Create Invoice
        </a>

        <div class="card shadow-sm">
            <div class="card-body p-0">

                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Source</th> {{-- 👈 important --}}
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->id }}</td>

                                <td>{{ $invoice->supplier->name ?? '' }}</td>

                                <td>{{ $invoice->date }}</td>

                                {{-- SOURCE --}}
                                <td>
                                    @if($invoice->source_type === 'purchase_order')
                                        PO #{{ $invoice->source_id }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-{{ $invoice->status == 'draft' ? 'secondary' : 'success' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">

                                        {{-- EDIT --}}
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('purchase-invoices.edit', $invoice->id) }}"
                                                class="btn btn-sm btn-warning">
                                                Edit
                                            </a>
                                        @endif

                                        {{-- POST --}}
                                        @if($invoice->status === 'draft')
                                            <form action="{{ route('purchase-invoices.post', $invoice->id) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-sm btn-success">Post</button>
                                            </form>
                                        @endif

                                        {{-- DELETE --}}
                                        @if($invoice->status === 'draft')
                                            <form action="{{ route('purchase-invoices.destroy', $invoice->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this invoice?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        @endif

                                        {{-- CANCEL --}}
                                        @if($invoice->status === 'posted')
                                            <form action="{{ route('purchase-invoices.cancel', $invoice->id) }}" method="POST"
                                                onsubmit="return confirm('Cancel this invoice?');">
                                                @csrf
                                                <button class="btn btn-sm btn-danger">Cancel</button>
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

        <div class="mt-3">
            {{ $invoices->links() }}
        </div>

    </div>
@endsection