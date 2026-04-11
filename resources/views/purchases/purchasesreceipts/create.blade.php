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
                <label>Receipt Date</label>
                <input type="date" name="receipt_date" class="form-control" required>
            </div>

            <h4>Products</h4>

            <table class="table" id="lines-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th width="150">Quantity</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <button type="button" class="btn btn-secondary" id="add-line">
                Add Product
            </button>

            <button type="submit" class="btn btn-primary">
                Save Receipt
            </button>
        </form>
    </div>

    @include('modals.product-picker')
@endsection

@push('scripts')
    <script>
        let lineIndex = 0;

        $('#add-line').on('click', function () {
            $('#productModal').modal('show');
        });

        $(document).on('click', '.select-product', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');

            const row = `
                <tr>
                    <td>
                        ${name}
                        <input type="hidden" name="lines[${lineIndex}][product_id]" value="${id}">
                    </td>
                    <td>
                        <input type="number"
                               name="lines[${lineIndex}][quantity]"
                               class="form-control"
                               min="1"
                               value="1"
                               required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-line">
                            Remove
                        </button>
                    </td>
                </tr>
            `;

            $('#lines-table tbody').append(row);
            lineIndex++;

            $('#productModal').modal('hide');
        });

        $(document).on('click', '.remove-line', function () {
            $(this).closest('tr').remove();
        });
    </script>
@endpush