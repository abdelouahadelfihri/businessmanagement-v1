@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Purchase Receipt #{{ $receipt->id }}</h1>

    <form action="{{ route('purchase-receipts.update', $receipt->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Warehouse</label>
            <select name="warehouse_id" class="form-control" required>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @if($warehouse->id == $receipt->warehouse_id) selected @endif>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-control">
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @if($supplier->id == $receipt->supplier_id) selected @endif>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Receipt Date</label>
            <input type="date" name="receipt_date" class="form-control" value="{{ $receipt->receipt_date }}" required>
        </div>

        <h4>Products</h4>
        <table class="table" id="lines-table">
            <thead>
                <tr><th>Product</th><th>Quantity</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($receipt->lines as $i => $line)
                <tr>
                    <td>
                        <input type="hidden" name="lines[{{ $i }}][product_id]" value="{{ $line->product_id }}">
                        {{ $line->product->name }}
                    </td>
                    <td><input type="number" name="lines[{{ $i }}][quantity]" value="{{ $line->quantity }}" class="form-control" min="1" required></td>
                    <td><button type="button" class="btn btn-danger remove-line">Remove</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="add-line">Add Product</button>
        <button type="submit" class="btn btn-primary">Update Receipt</button>
    </form>
</div>

@include('purchase_receipts.partials.product_modal')

<script>
let lineCount = {{ $receipt->lines->count() }};

$(document).on('click', '.remove-line', function() {
    $(this).closest('tr').remove();
});

document.getElementById('add-line').addEventListener('click', function() {
    $('#productModal').modal('show');
});

$(document).on('click', '.select-product', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const row = `
        <tr>
            <td>
                <input type="hidden" name="lines[${lineCount}][product_id]" value="${id}">
                ${name}
            </td>
            <td><input type="number" name="lines[${lineCount}][quantity]" class="form-control" min="1" required></td>
            <td><button type="button" class="btn btn-danger remove-line">Remove</button></td>
        </tr>`;
    $('#lines-table tbody').append(row);
    lineCount++;
    $('#productModal').modal('hide');
});
</script>
@endsection