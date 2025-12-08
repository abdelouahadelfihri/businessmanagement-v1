@extends('layouts.app')

@section('content')
<h2>Select Supplier</h2>

@if($suppliers->count() == 0)
    <div class="alert alert-info">No suppliers found.</div>
    <a href="{{ route('suppliers.create', ['redirect' => $redirect, 'id' => $id]) }}"
       class="btn btn-primary">Add Supplier</a>
@else

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Select</th>
        </tr>
    </thead>
    <tbody>
        @foreach($suppliers as $supplier)
        <tr>
            <td>{{ $supplier->name }}</td>
            <td>
                @php
                    $params = [
                        'supplier_id' => $supplier->id,
                        'supplier_name' => $supplier->name,
                    ];

                    if ($id) {
                        $params['id'] = $id;
                    }
                @endphp

                <a class="btn btn-success"
                   href="{{ route($redirect, $params) }}">
                    Select
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('suppliers.create', ['redirect' => $redirect, 'id' => $id]) }}"
   class="btn btn-primary mt-2">
    Add Supplier
</a>

@endif

@endsection