@extends('layouts.app')
@section('title','Purchase Requests')
@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Purchase Requests</h3>
  <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">Create Request</a>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th>Supplier</th><th>Description</th><th>Date</th><th>Status</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($purchaseRequests as $pr)
    <tr>
      <td>{{ $pr->supplier?->name }}</td>
      <td>{{ $pr->description }}</td>
      <td>{{ $pr->date->format('Y-m-d') }}</td>
      <td>{{ ucfirst($pr->status) }}</td>
      <td class="d-flex gap-1">
        <a href="{{ route('purchase-requests.edit',$pr) }}" class="btn btn-sm btn-outline-primary">Edit</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection