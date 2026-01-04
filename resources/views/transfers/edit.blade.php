@extends('layouts.app')
@section('content')
<h3>Transfer {{ $transfer->transfer_number }}</h3>

<p><strong>From:</strong> {{ $transfer->fromWarehouse->name }}</p>
<p><strong>To:</strong> {{ $transfer->toWarehouse->name }}</p>
<p><strong>Date:</strong> {{ $transfer->transfer_date }}</p>

<table class="table table-bordered">
<thead>
<tr><th>Product</th><th>Qty</th></tr>
</thead>
<tbody>
@foreach($transfer->lines as $line)
<tr>
<td>{{ $line->product->name }}</td>
<td>{{ $line->quantity }}</td>
</tr>
@endforeach
</tbody>
</table>

<a href="{{ route('transfers.index') }}" class="btn btn-secondary">Back</a>
@endsection