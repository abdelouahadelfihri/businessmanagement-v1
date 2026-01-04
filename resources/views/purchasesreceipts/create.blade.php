@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Purchase Receipt</h1>

    <form action="{{ route('purchase-receipts.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Warehouse</label>
            <select name="warehouse_id" class="form-control" required>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-control">
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Receipt Date</label>
            <input type="date" name="receipt_date" class="form-control" required>
        </div>

        <h4>Products</h4>
        <table class="table" id="lines-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="add-line">Add Product</button>
        <button type="submit" class="btn btn-primary">Save Receipt</button>
    </form>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5>Select Product</h5></div>
            <div class="modal-body">
                <table class="table table-bordered" id="product-list">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success select-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}">Select</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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