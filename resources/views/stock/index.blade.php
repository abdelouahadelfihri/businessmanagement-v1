{{-- resources/views/stock/index.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h3>Stock Overview</h3>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th width="150">Stock</th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td><strong>{{ $product->current_stock }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection