@extends('layouts.app')

@section('title','Edit Purchase Request')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Edit Purchase Request</h3>
  <div>
    <a href="{{ route('purchase-requests.index') }}" class="btn btn-outline-secondary">Back to list</a>
  </div>
</div>

<form method="POST" action="{{ route('purchase-requests.update', $purchaseRequest) }}">
  @csrf
  @method('PUT')

  <div class="mb-3">
    <label class="form-label">Supplier</label>
    <div class="input-group">
      <select name="supplier_id" id="supplierSelect" class="form-select">
        <option value="">-- No supplier --</option>
        @forelse($suppliers as $s)
          <option value="{{ $s->id }}" @selected(old('supplier_id', $purchaseRequest->supplier_id) == $s->id)>{{ $s->name }} @if($s->email) ({{ $s->email }}) @endif</option>
        @empty
          <option disabled>No suppliers available</option>
        @endforelse
      </select>

      <button type="button" class="btn btn-outline-secondary" id="openSuppliersList" title="Open suppliers list to pick">Pick</button>
      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">Add Supplier</button>
    </div>
    @error('supplier_id')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>

  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4">{{ old('description', $purchaseRequest->description) }}</textarea>
    @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label class="form-label">Date</label>
      <input type="date" name="date" class="form-control" value="{{ old('date', $purchaseRequest->date->format('Y-m-d')) }}">
      @error('date')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="draft" @selected(old('status', $purchaseRequest->status) == 'draft')>Draft</option>
        <option value="submitted" @selected(old('status', $purchaseRequest->status) == 'submitted')>Submitted</option>
        <option value="approved" @selected(old('status', $purchaseRequest->status) == 'approved')>Approved</option>
        <option value="rejected" @selected(old('status', $purchaseRequest->status) == 'rejected')>Rejected</option>
      </select>
      @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary">Save changes</button>
    <button type="button" class="btn btn-outline-danger" id="showDeleteBtn">Delete</button>
  </div>
</form>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addSupplierForm" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Add Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="addSupplierAlert"></div>

        <div class="mb-3">
          <label class="form-label">Name *</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add supplier</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteRequestForm" method="POST" action="{{ route('purchase-requests.destroy', $purchaseRequest) }}">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-body">
          <h5>Confirm delete</h5>
          <p>Are you sure you want to delete this purchase request? This cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  // Open supplier list popup to pick
  document.getElementById('openSuppliersList').addEventListener('click', function(e){
    e.preventDefault();
    const w = window.open("{{ route('suppliers.index') }}?popup=1", "suppliers_pick", "width=900,height=700");
    window.receivePickedSupplier = function(supplier){
      const sel = document.getElementById('supplierSelect');
      let option = sel.querySelector('option[value="'+supplier.id+'"]');
      if(!option){
        option = document.createElement('option');
        option.value = supplier.id;
        option.text = supplier.name;
        sel.appendChild(option);
      }
      sel.value = supplier.id;
      if(w && !w.closed) w.close();
    };
    window.addEventListener('message', function(ev){
      if(ev.data?.type==='supplier-picked'){
        window.receivePickedSupplier(ev.data.supplier);
      }
    });
  });

  // AJAX Add supplier
  (function(){
    const addForm = document.getElementById('addSupplierForm');
    const modalEl = document.getElementById('addSupplierModal');
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    addForm.addEventListener('submit', async function(e){
      e.preventDefault();
      const formData = new FormData(addForm);
      const submitBtn = addForm.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      document.getElementById('addSupplierAlert').innerHTML = '';
      try {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch("{{ route('suppliers.storeQuick') }}", {
          method:'POST',
          headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'},
          body: formData
        });
        const data = await res.json();
        if(res.ok && data.success){
          const sel = document.getElementById('supplierSelect');
          const opt = document.createElement('option');
          opt.value = data.supplier.id;
          opt.text = data.supplier.name;
          sel.appendChild(opt);
          sel.value = data.supplier.id;
          bsModal.hide();
        } else {
          let html = '<div class="alert alert-danger">';
          if(data.errors){html+='<ul>';Object.values(data.errors).forEach(arr=>arr.forEach(m=>html+=`<li>${m}</li>`));html+='</ul>'}
          else html += data.message||'Could not create supplier.';
          html+='</div>';
          document.getElementById('addSupplierAlert').innerHTML = html;
        }
      } catch(err){document.getElementById('addSupplierAlert').innerHTML='<div class="alert alert-danger">Network error.</div>';}
      finally{submitBtn.disabled=false;}
    });
  })();

  // Delete modal
  document.getElementById('showDeleteBtn').addEventListener('click', ()=>bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmModal')).show());
</script>
@endpush
@endsection