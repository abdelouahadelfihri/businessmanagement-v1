@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Create Purchase Order</h1>

    <a class="btn btn-secondary mb-3"
       href="{{ route('suppliers.index', ['select_for' => 'purchase-order', 'return_url' => url()->current()]) }}">
       Pick Supplier
    </a>

    <a class="btn btn-secondary mb-3"
       href="{{ route('purchase-requests.index', ['select_for' => 'purchase-order', 'return_url' => url()->current()]) }}">
       Pick Purchase Request
    </a>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('purchase-orders.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select" required>
                        <option value="">-- choose supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}"
                                {{ $selectedSupplierId == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Purchase Request</label>
                    <select name="purchase_request_id" id="request_id" class="form-select" required>
                        <option value="">-- choose request --</option>
                        @foreach($requests as $r)
                            <option value="{{ $r->id }}"
                                {{ $selectedRequestId == $r->id ? 'selected' : '' }}>
                                {{ $r->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Order Date</label>
                    <input type="date" name="order_date" 
                           class="form-control" 
                           value="{{ date('Y-m-d') }}" required>
                </div>

                <button class="btn btn-primary">Save</button>
            </form>

        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const params = new URLSearchParams(window.location.search);

    if (params.get("selected_supplier_id")) {
        document.getElementById("supplier_id").value = params.get("selected_supplier_id");
    }
    if (params.get("selected_request_id")) {
        document.getElementById("request_id").value = params.get("selected_request_id");
    }
});
</script>

@endsection