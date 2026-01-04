@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Transfer #{{ $transfer->id }}</h1>

    <form action="{{ route('transfers.update', $transfer->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>From Warehouse</label>
            <select name="from_warehouse_id" class="form-control" required>
                @foreach(App\Models\MasterData\Warehouse::all() as $w)
                <option value="{{ $w->id }}" @if($w->id == $transfer->from_warehouse_id) selected @endif>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>To Warehouse</label>
            <select name="to_warehouse_id" class="form-control" required>
                @foreach(App\Models\MasterData\Warehouse::all() as $w)
                <option value="{{ $w->id }}" @if($w->id == $transfer->to_warehouse_id) selected @endif>{{ $w->name }}</option>
                @endforeach
            </select>
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
            <tbody>
                @foreach($transfer->lines as $i => $line)
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
        <button type="submit" class="btn btn-primary">Update Transfer</button>
    </form>
</div>

<!-- Reuse same product modal and JS as in create.blade.php -->
@include('transfers.partials.product_modal')
<script>
let lineCount = {{ $transfer->lines->count() }};

// same JS code as create.blade.php
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