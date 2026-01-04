@extends('layouts.app')
@section('content')
<h3>Create Purchase Order</h3>
<form method="POST" action="{{ route('purchase-orders.store') }}">
@csrf

<div class="mb-3">
    <label>Supplier</label>
    <div class="input-group">
        <input type="text" id="supplier_name" class="form-control" readonly placeholder="Select Supplier">
        <input type="hidden" name="supplier_id" id="supplier_id">
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#supplierModal">Pick Supplier</button>
    </div>
</div>

<table class="table" id="lines">
<thead>
<tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th></th></tr>
</thead>
<tbody></tbody>
</table>

<button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#productModal">Add Product</button>
<button type="submit" class="btn btn-success">Save</button>
</form>

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5>Select Supplier</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<table class="table table-hover">
@foreach($suppliers as $s)
<tr style="cursor:pointer" onclick="selectSupplier({{ $s->id }}, '{{ $s->name }}')"><td>{{ $s->name }}</td></tr>
@endforeach
</table>
</div></div></div></div>

<!-- Product Modal -->
<div class="modal fade" id="productModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5>Select Product</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<table class="table table-hover">
@foreach($products as $p)
<tr style="cursor:pointer" onclick="addLine({{ $p->id }}, '{{ $p->name }}', {{ $p->price }})">
<td>{{ $p->name }}</td><td>{{ $p->price }}</td></tr>
@endforeach
</table>
</div></div></div></div>

@endsection

@section('scripts')
<script>
let i=0;
function selectSupplier(id,name){
    document.getElementById('supplier_id').value=id;
    document.getElementById('supplier_name').value=name;
    bootstrap.Modal.getInstance(document.getElementById('supplierModal')).hide();
}
function addLine(id,name,price){
    document.querySelector('#lines tbody').insertAdjacentHTML('beforeend',`
<tr>
<td><input type="hidden" name="lines[${i}][product_id]" value="${id}">${name}</td>
<td><input type="number" name="lines[${i}][quantity]" class="form-control" value="1"></td>
<td><input type="number" name="lines[${i}][unit_price]" class="form-control" value="${price}" step="0.01"></td>
<td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">X</button></td>
</tr>
`);
i++;
bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
}
</script>
@endsection