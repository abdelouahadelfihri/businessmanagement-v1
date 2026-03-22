@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between mb-3">
            <h3>Edit Purchase Order #{{ $order->po_number }}</h3>
            <a href="{{ route('purchasesorders.index') }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form method="POST" action="{{ route('purchasesorders.update', $order->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- HEADER --}}
                    <div class="row mb-3">

                        {{-- Supplier --}}
                        <div class="col-md-4">
                            <label>Supplier</label>
                            <div class="input-group">
                                <input type="hidden" name="supplier_id" value="{{ $order->supplier_id }}">
                                <input type="text" class="form-control" value="{{ $order->supplier->name }}" readonly>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                    data-bs-target="#supplierModal">
                                    Select
                                </button>
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="col-md-3">
                            <label>Order Date</label>
                            <input type="date" name="order_date" value="{{ $order->order_date }}" class="form-control"
                                required>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="draft" {{ $order->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed
                                </option>
                            </select>
                        </div>

                    </div>

                    {{-- ADD PRODUCT --}}
                    <button type="button" class="btn btn-secondary mb-2" id="add-line">
                        + Add Product
                    </button>

                    {{-- LINES --}}
                    <table class="table table-bordered" id="lines-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th width="120">Qty</th>
                                <th width="150">Unit Price</th>
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->lines as $i => $line)
                                <tr>
                                    <td>
                                        {{ $line->product->name }}
                                        <input type="hidden" name="lines[{{ $i }}][product_id]" class="product-id"
                                            value="{{ $line->product_id }}">
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $i }}][quantity]" value="{{ $line->quantity }}"
                                            class="form-control" min="1">
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $i }}][unit_price]" value="{{ $line->unit_price }}"
                                            class="form-control" step="0.01">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-line">×</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- ACTIONS --}}
                    <div class="text-end">
                        <button class="btn btn-primary">Update</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @include('modals.product-picker')
    @include('modals.supplier-picker')
@endsection

@push('scripts')
    <script>
        let lineIndex = 0;

        // Open product modal
        $('#add-line').on('click', function () {
            $('#productModal').modal('show');
        });

        // Select product
        $(document).on('click', '.select-product', function () {
            const productId = $(this).data('id');
            const productName = $(this).data('name');

            // Prevent duplicate product
            let exists = false;
            $('.product-id').each(function () {
                if ($(this).val() == productId) {
                    exists = true;
                }
            });

            if (exists) {
                alert('This product is already added. Change quantity instead.');
                return;
            }

            const row = `
                <tr>
                    <td>
                        ${productName}
                        <input type="hidden"
                               name="lines[${lineIndex}][product_id]"
                               class="product-id"
                               value="${productId}">
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
                        <input type="number"
                               name="lines[${lineIndex}][unit_price]"
                               class="form-control"
                               step="0.01"
                               required>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-danger btn-sm remove-line">
                            ×
                        </button>
                    </td>
                </tr>
            `;

            $('#lines-table tbody').append(row);
            lineIndex++;

            $('#productModal').modal('hide');
        });

        // Remove line
        $(document).on('click', '.remove-line', function () {
            $(this).closest('tr').remove();
        });
    </script>
@endpush