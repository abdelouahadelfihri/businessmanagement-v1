@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Sales Invoice</h1>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(!$saleOrder)
            {{-- Step 1: choose the order to invoice --}}
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Which order do you want to invoice?</div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <div class="alert alert-info mb-0">
                            No order is waiting for invoicing. Orders must be <strong>confirmed</strong>,
                            <strong>processing</strong>, <strong>shipped</strong> or <strong>delivered</strong>
                            to be invoiced.
                        </div>
                    @else
                        <form method="GET" action="{{ route('sales.salesinvoices.create') }}"
                            class="row g-2 align-items-end">
                            <div class="col-md-9">
                                <label class="form-label mb-1">Sales order *</label>
                                <select name="sales_order_id" class="form-select" required>
                                    <option value="">Select an order</option>
                                    @foreach($orders as $order)
                                        <option value="{{ $order->id }}">
                                            {{ $order->order_number }} — {{ $order->customer->name ?? '—' }}
                                            ({{ ucfirst($order->status) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    Continue <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('sales.salesinvoices.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        @else
            {{-- Step 2: the invoice form --}}
            <form method="POST" action="{{ route('sales.salesinvoices.store') }}">
                @csrf
                @include('sales.salesinvoices._form')
            </form>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
