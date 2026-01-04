@extends('layouts.app')
@section('content')
<h3>Create Purchase Receipt</h3>
<form method="POST" action="{{ route('purchase-receipts.store') }}">
@csrf

<div class="mb-3">
<label>Purchase Order</label>
<div class="input-group">
<input type="text" id="po_number" class="form-control" readonly placeholder="Pick PO">
<input type="hidden" name="purchase_order_id" id="purchase_order_id">
<button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#poModal">Pick PO</button>
</div>
</div>

<input type="date" name="receipt_date" class="form-control mb-3" required>

<table class="table" id="lines">
<thead><tr><th>Product</th><th>Quantity</th><th></th></tr></thead>
<tbody></tbody>
</table>

<button type="submit" class="btn btn-success">Save</button>
</form>

<!-- PO Modal -->
<div class="modal fade" id="poModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5>Select Purchase Order</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<table class="table table-hover">
@foreach($purchaseOrders as $po)
<tr style="cursor:pointer" onclick="selectPO({{ $po->id }}, '{{ $po->po_number }}', {{ json_encode($po->lines) }})">
<td>{{ $po->po_number }}</td><td>{{ $po->supplier->name }}</td>
</tr>
@endforeach
</table>
</div></div></div></div>

@endsection

@section('scripts')
<script>
let i=0;
function selectPO(id,number,lines){
    document.getElementById('purchase_order_id').value=id;
    document.getElementById('po_number').value=number;
    let tbody = document.querySelector('#lines tbody');
    tbody.innerHTML = '';
    lines.forEach(line=>{
        tbody.insertAdjacentHTML('beforeend',`
<tr>
<td><input type="hidden" name="lines[${i}][product_id]" value="${line.product_id}">${line.product.name}</td>
<td><input type="number" name="lines[${i}][quantity]" class="form-control" value="${line.quantity}"></td>
<td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">X</button></td>
</tr>`);
        i++;
    });
    bootstrap.Modal.getInstance(document.getElementById('poModal')).hide();
}
</script>
@endsection