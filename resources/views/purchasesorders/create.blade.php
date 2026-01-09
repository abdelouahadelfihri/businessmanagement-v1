@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Create Purchase Order</h3>

        <form method="POST" action="{{ route('purchase-orders.store') }}">
            @csrf

            {{-- HEADER --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control" required>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Order Date</label>
                    <input type="date" name="order_date" class="form-control" required>
                </div>
            </div>

            {{-- LINES --}}
            <table class="table table-bordered" id="linesTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th width="120">Qty</th>
                        <th width="150">Unit Price</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <button type="button" class="btn btn-secondary" onclick="addLine()">+ Add Line</button>

            <div class="mt-3">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>

    {{-- PRODUCT MODAL --}}
    @include('partials.product-modal')
@endsection

@push('scripts')
    <script>
        let lineIndex = 0;

        function addLine() {
            const row = `
                <tr>
                    <td>
                        <input type="hidden" name="lines[${lineIndex}][product_id]" class="product-id">
                        <input type="text" class="form-control product-name" readonly
                               onclick="openProductModal(this)" placeholder="Select product">
                    </td>
                    <td>
                        <input type="number" name="lines[${lineIndex}][quantity]" class="form-control" min="1" required>
                    </td>
                    <td>
                        <input type="number" name="lines[${lineIndex}][unit_price]" class="form-control" step="0.01" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">×</button>
                    </td>
                </tr>`;
            document.querySelector('#linesTable tbody').insertAdjacentHTML('beforeend', row);
            lineIndex++;
        }

        let currentProductInput = null;

        function openProductModal(input) {
            currentProductInput = input;
            $('#productModal').modal('show');
        }

        function selectProduct(id, name) {
            currentProductInput.value = name;
            currentProductInput.closest('td').querySelector('.product-id').value = id;
            $('#productModal').modal('hide');
        }
    </script>
@endpush