@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h3>{{ $title ?? 'Create Document' }}</h3>

        <form method="POST" action="{{ $route }}">
            @csrf

            {{-- HEADER --}}
            <div class="row mb-3">
                {{-- Warehouse --}}
                <div class="col-md-4">
                    <label>Warehouse</label>
                    <select name="warehouse_id" class="form-control" required>
                        <option value="">-- Select Warehouse --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                {{ isset($model) && $model->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Supplier / Customer --}}
                <div class="col-md-4">
                    <label>{{ $partyLabel }}</label>
                    <div class="input-group">
                        <input type="hidden" name="{{ $partyField }}" id="party_id">
                        <input type="text" id="party_name" class="form-control" readonly>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#partyModal">
                            Select
                        </button>
                    </div>
                </div>

                {{-- Date --}}
                <div class="col-md-3">
                    <label>Date</label>
                    <input type="date" name="{{ $dateField ?? 'date' }}" class="form-control" required>
                </div>

                {{-- Status --}}
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                    </select>
                </div>

            </div>

            {{-- ADD PRODUCT --}}
            <button type="button" id="add-line" class="btn btn-secondary mb-2">
                + Add Product
            </button>

            {{-- LINES --}}
            <table class="table table-bordered" id="lines-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th width="120">Qty</th>

                        @if($withPrice)
                            <th width="150">Unit Price</th>
                        @endif

                        <th width="60"></th>
                    </tr>
                </thead>

                <tbody>

                    {{-- LOAD FROM SOURCE --}}
                    @if(isset($source) && $source)
                        @foreach($source->lines as $i => $line)
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

                                @if($withPrice)
                                    <td>
                                        <input type="number" name="lines[{{ $i }}][unit_price]" value="{{ $line->unit_price ?? 0 }}"
                                            class="form-control" step="0.01">
                                    </td>
                                @endif

                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-line">×</button>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                </tbody>
            </table>

            {{-- ACTIONS --}}
            <div class="text-end">
                <button class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>

    @include('modals.product-picker')
    @include('modals.party-picker') {{-- reusable modal --}}
@endsection


@push('scripts')
    <script>
        let lineIndex = {{ isset($source) ? count($source->lines) : 0 }};

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
                           class="form-control"
                           value="1" min="1">
                </td>

                @if($withPrice)
                    <td>
                        <input type="number"
                               name="lines[${lineIndex}][unit_price]"
                               class="form-control" step="0.01">
                    </td>
                @endif

                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-line">×</button>
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
<?php
// Done
<?>