@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Purchase Order</h1>

    <form action="{{ route('purchase-orders.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-control" required>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Order Date</label>
            <input type="date" name="order_date" class="form-control" required>
        </div>

        <h4>Products</h4>
        <table class="table" id="lines-table">
            <thead>
                <tr><th>Product</th><th>Quantity</th><th>Action</th></tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="add-line">Add Product</button>
        <button type="submit" class="btn btn-primary">Save Order</button>
    </form>
</div>

@include('partials.product_modal')

<script>
let lineCount = 0;

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

$(document).on('click', '.remove-line', function() {
    $(this).closest('tr').remove();
});
</script>
@endsection