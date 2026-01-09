@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Create Purchase Order</h3>

        <form method="POST" action="{{ route('purchasesorders.store') }}">
            @csrf

            {{-- HEADER --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Supplier</label>
                    <input type="hidden" name="supplier_id" id="supplier_id">
                    <input type="text" id="supplier_name" class="form-control" readonly>
                    <button type="button" class="btn btn-secondary mt-2" data-bs-toggle="modal"
                        data-bs-target="#supplierModal">Select Supplier</button>
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
    @include('modals.product-picker')
    {{-- SUPPLIER MODAL --}}
    @include('modals.supplier-picker')
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

            // collect selected product ids
            let selectedProducts = [];
            document.querySelectorAll('.product-id').forEach(input => {
                if (input.value) {
                    selectedProducts.push(input.value);
                }
            });

            // check duplicate
            if (selectedProducts.includes(String(id))) {
                alert('This product is already added. Please change the quantity instead.');
                return;
            }

            // assign product
            currentProductInput.value = name;
            currentProductInput.closest('td')
                .querySelector('.product-id').value = id;

            $('#productModal').modal('hide');
        }

    </script>
@endpush