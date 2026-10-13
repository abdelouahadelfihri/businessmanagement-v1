@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Sales Return</h1>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(!$saleOrder)
            {{-- Step 1: choose the order the goods come from --}}
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Which order is the customer returning goods from?</div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <div class="alert alert-info mb-0">
                            No order can be returned yet. Only orders with at least one
                            <strong>validated delivery</strong> can have a return.
                        </div>
                    @else
                        <form method="GET" action="{{ route('sales.salesreturns.create') }}"
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
                <a href="{{ route('sales.salesreturns.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        @else
            {{-- Step 2: the return form --}}
            <form method="POST" action="{{ route('sales.salesreturns.store') }}">
                @csrf
                @include('sales.salesreturns._form')
            </form>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
