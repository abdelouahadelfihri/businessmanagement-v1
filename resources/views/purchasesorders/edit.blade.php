@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Purchase Order</h3>

    <form method="POST" action="{{ route('purchase-orders.update', $order) }}">
        @csrf
        @method('PUT')

        {{-- HEADER --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Supplier</label>
                <select name="supplier_id" class="form-control">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            @selected($order->supplier_id == $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Order Date</label>
                <input type="date" name="order_date" class="form-control"
                       value="{{ $order->order_date }}">
            </div>
        </div>

        {{-- LINES --}}
        <table class="table table-bordered" id="linesTable">
            <tbody>
                @foreach($order->lines as $i => $line)
                <tr>
                    <td>
                        <input type="hidden" name="lines[{{ $i }}][product_id]"
                               value="{{ $line->product_id }}" class="product-id">
                        <input type="text" class="form-control product-name"
                               value="{{ $line->product->name }}" readonly
                               onclick="openProductModal(this)">
                    </td>
                    <td>
                        <input type="number" name="lines[{{ $i }}][quantity]"
                               value="{{ $line->quantity }}" class="form-control">
                    </td>
                    <td>
                        <input type="number" name="lines[{{ $i }}][unit_price]"
                               value="{{ $line->unit_price }}" class="form-control">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="this.closest('tr').remove()">×</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary" onclick="addLine()">+ Add Line</button>

        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

@include('modals.product-modal')
@endsection
