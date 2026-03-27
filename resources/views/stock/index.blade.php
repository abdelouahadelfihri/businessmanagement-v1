{{-- resources/views/stock/index.blade.php --}}
{{-- Done --}}

@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h3>Stock Overview</h3>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Total</th>
                    <th>Details</th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td><strong>{{ $product->current_stock }}</strong></td>
                        <td>
                            @foreach($product->warehouseStocks as $stock)
                                <div>
                                    {{ $stock->warehouse->name }} : {{ $stock->quantity }}
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection