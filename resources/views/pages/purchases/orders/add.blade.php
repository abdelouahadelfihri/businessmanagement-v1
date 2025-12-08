@extends('layouts.app')

@section('title', 'Add Purchase Order')

@section('content')
<h1>Add Purchase Order</h1>

<form id="addPurchaseOrderForm">
    @csrf
    <div class="mb-3">
        <label for="supplier_id" class="form-label">Supplier ID</label>
        <input type="number" class="form-control" id="supplier_id" name="supplier_id" required>
    </div>
    <div class="mb-3">
        <label for="request_id" class="form-label">Purchase Request ID (optional)</label>
        <input type="number" class="form-control" id="request_id" name="request_id">
    </div>
    <div class="mb-3">
        <label for="order_date" class="form-label">Order Date (timestamp)</label>
        <input type="number" class="form-control" id="order_date" name="order_date" required>
    </div>
    <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <input type="text" class="form-control" id="status" name="status" required>
    </div>
    <div class="mb-3">
        <label for="total_amount" class="form-label">Total Amount</label>
        <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
    </div>
    <button type="submit" class="btn btn-primary">Add Purchase Order</button>
</form>

<div id="message" class="mt-3"></div>

<script>
document.getElementById('addPurchaseOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        supplier_id: document.getElementById('supplier_id').value,
        request_id: document.getElementById('request_id').value,
        order_date: document.getElementById('order_date').value,
        status: document.getElementById('status').value,
        total_amount: document.getElementById('total_amount').value,
    };

    try {
        const response = await fetch('/api/purchase-orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        document.getElementById('message').innerText = 'Purchase Order added successfully!';
        console.log(data);
    } catch (error) {
        document.getElementById('message').innerText = 'Error adding Purchase Order.';
        console.error(error);
    }
});
</script>
@endsection