@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h3>{{ $title ?? 'Edit Document' }}</h3>

        <form method="POST" action="{{ $route }}">
            @csrf
            @method('PUT')

            {{-- HEADER --}}
            <div class="row mb-3">

                <div class="col-md-4">
                    <label>Warehouse</label>
                    <select name="warehouse_id" class="form-control" required>
                        <option value="">-- Select Warehouse --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ isset($model) && $model->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>{{ $partyLabel }}</label>
                    <div class="input-group">
                        <input type="hidden" name="{{ $partyField }}" value="{{ $model->$partyField }}">
                        <input type="text" class="form-control" value="{{ $model->party->name ?? '' }}" readonly>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#partyModal">
                            Select
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    <label>Date</label>
                    <input type="date" name="{{ $dateField ?? 'date' }}" value="{{ $model->{$dateField ?? 'date'} }}"
                        class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" {{ $model->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ $model->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    </select>
                </div>

            </div>

            <button type="button" id="add-line" class="btn btn-secondary mb-2">
                + Add Product
            </button>

            <table class="table table-bordered" id="lines-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>

                        @if($withPrice)
                            <th>Unit Price</th>
                        @endif

                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($model->lines as $i => $line)
                        <tr>
                            <td>
                                {{ $line->product->name }}
                                <input type="hidden" name="lines[{{ $i }}][product_id]" class="product-id"
                                    value="{{ $line->product_id }}">
                            </td>

                            <td>
                                <input type="number" name="lines[{{ $i }}][quantity]" value="{{ $line->quantity }}"
                                    class="form-control">
                            </td>

                            @if($withPrice)
                                <td>
                                    <input type="number" name="lines[{{ $i }}][unit_price]" value="{{ $line->unit_price }}"
                                        class="form-control">
                                </td>
                            @endif

                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-line">×</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-end">
                <button class="btn btn-primary">Update</button>
            </div>

        </form>
    </div>

    @include('modals.product-picker')
    @include('modals.party-picker')
@endsection


@push('scripts')
    <script>

        let lineIndex = {{ $model->lines->count() }};

        $('#add-line').click(function () {
            $('#productModal').modal('show');
        });

        $(document).on('click', '.select-product', function () {

            let id = $(this).data('id');
            let name = $(this).data('name');

            let row = `
                        <tr>
                            <td>
                                ${name}
                                <input type="hidden"
                                       name="lines[${lineIndex}][product_id]"
                                       class="product-id"
                                       value="${id}">
                            </td>

                            <td>
                                <input type="number"
                                       name="lines[${lineIndex}][quantity]"
                                       class="form-control" value="1">
                            </td>

                            @if($withPrice)
                                <td>
                                    <input type="number"
                                           name="lines[${lineIndex}][unit_price]"
                                           class="form-control">
                                </td>
                            @endif

                            <td>
                                <button type="button"
                                        class="btn btn-danger btn-sm remove-line">×</button>
                            </td>
                        </tr>
                    `;

            $('#lines-table tbody').append(row);
            lineIndex++;
        });

        $(document).on('click', '.remove-line', function () {
            $(this).closest('tr').remove();
        });

    </script>
@endpush
<?php
// Done
?>