@extends('layouts.app')
@section('title','Suppliers')
@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Suppliers</h3>
  <a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th>Name</th><th>Email</th><th>Phone</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($suppliers as $s)
    <tr>
      <td>{{ $s->name }}</td>
      <td>{{ $s->email }}</td>
      <td>{{ $s->phone }}</td>
      <td class="d-flex gap-1">
        <a href="{{ route('suppliers.edit',$s) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        @if(request()->popup)
          <button type="button" class="btn btn-sm btn-success" onclick="pickSupplier({{ $s->id }},'{{ $s->name }}')">Pick</button>
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

@push('scripts')
@if(request()->popup)
<script>
  function pickSupplier(id,name){
    const supplier = {id,name};
    if(window.opener?.receivePickedSupplier) window.opener.receivePickedSupplier(supplier);
    window.opener?.postMessage({type:'supplier-picked',supplier},'*');
    window.close();
  }
</script>
@endif
@endpush
@endsection