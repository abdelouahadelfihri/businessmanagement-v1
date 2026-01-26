@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Edit Purchase Order</h3>
            <a href="{{ route('purchasesorders.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('purchase-orders.update', $order) }}">
                    @csrf
                    @method('PUT')

                    {{-- HEADER --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Supplier</label>
                            <div class="input-group">
                                <input type="hidden" name="supplier_id" id="supplier_id">
                                <input type="text" id="supplier_name" class="form-control" readonly>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                    data-bs-target="#supplierModal">Select</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label>Order Date</label>
                            <input type="date" name="order_date" class="form-control" required>
                        </div>
                    </div>

                    {{-- ADD LINE BUTTON --}}
                    <div class="mb-2">
                        <button type="button" class="btn btn-secondary" onclick="addLine()">+ Add Line</button>
                    </div>

                    {{-- LINES --}}
                    <table class="table table-bordered" id="linesTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th width="120">Quantity</th>
                                <th width="150">Unit Price</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->lines as $i => $line)
                                <tr>
                                    <td>
                                        <input type="hidden" name="lines[{{ $i }}][product_id]" value="{{ $line->product_id }}"
                                            class="product-id">

                                        <input type="text" class="form-control product-name" value="{{ $line->product->name }}"
                                            readonly onclick="openProductModal(this)" placeholder="Select product">
                                    </td>

                                    <td>
                                        <input type="number" name="lines[{{ $i }}][quantity]" value="{{ $line->quantity }}"
                                            min="1" class="form-control" required>
                                    </td>

                                    <td>
                                        <input type="number" name="lines[{{ $i }}][unit_price]" value="{{ $line->unit_price }}"
                                            step="0.01" class="form-control" required>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="this.closest('tr').remove()">
                                            ×
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <a href="{{ route('purchasesorders.index') }}" class="btn btn-light">
                            Cancel
                        </a>
                        <button class="btn btn-primary">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- PRODUCT PICKER MODAL --}}
    @include('modals.product-picker')
@endsection

@push('scripts')
    <script>
        let lineIndex = {{ $order->lines->count() }};
        let currentProductInput = null;

        function addLine() {
            const row = `
                                                    <tr>
                                                        <td>
                                                            <input type="hidden"
                                                                name="lines[${lineIndex}][product_id]"
                                                                class="product-id">

                                                            <input type="text"
                                                                class="form-control product-name"
                                                                readonly
                                                                onclick="openProductModal(this)"
                                                                placeholder="Select product">
                                                        </td>

                                                        <td>
                                                            <input type="number"
                                                                name="lines[${lineIndex}][quantity]"
                                                                min="1"
                                                                class="form-control"
                                                                required>
                                                        </td>

                                                        <td>
                                                            <input type="number"
                                                                name="lines[${lineIndex}][unit_price]"
                                                                step="0.01"
                                                                class="form-control"
                                                                required>
                                                        </td>

                                                        <td>
                                                            <button type="button"
                                                                    class="btn btn-danger btn-sm"
                                                                    onclick="this.closest('tr').remove()">
                                                                ×
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    `;
            document.querySelector('#linesTable tbody')
                .insertAdjacentHTML('beforeend', row);

            lineIndex++;
        }

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