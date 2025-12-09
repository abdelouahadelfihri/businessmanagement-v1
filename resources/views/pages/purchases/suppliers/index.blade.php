@extends('layouts.app')

@section('title','Suppliers')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h3>Suppliers</h3>
  <div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
  </div>
</div>

<form method="GET" class="row g-2 mb-3">
  <div class="col-auto">
    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Search suppliers...">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary">Search</button>
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-light">Reset</a>
  </div>
</form>

<table class="table table-hover">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email / Phone</th>
      <th>Address</th>
      <th class="text-end">Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse($suppliers as $supplier)
      <tr>
        <td>{{ $supplier->name }}</td>
        <td>
          <div>{{ $supplier->email }}</div>
          <div class="text-muted small">{{ $supplier->phone }}</div>
        </td>
        <td>{{ $supplier->address }}</td>
        <td class="text-end table-actions">
          <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary">Edit</a>

          <!-- Pick button: if purchase request form opened, you can use window.opener or message passing.
               We'll use a data attribute so JS can pick supplier when needed -->
          <button type="button" class="btn btn-sm btn-success pick-supplier-btn"
                  data-id="{{ $supplier->id }}" data-name="{{ $supplier->name }}">
            Pick
          </button>

          <button class="btn btn-sm btn-danger delete-btn" data-action="{{ route('suppliers.destroy', $supplier) }}">Delete</button>
        </td>
      </tr>
    @empty
      <tr><td colspan="4" class="text-center">No suppliers found. <a href="{{ route('suppliers.create') }}">Add a supplier</a></td></tr>
    @endforelse
  </tbody>
</table>

<div class="d-flex justify-content-between align-items-center">
  <div>
    Showing {{ $suppliers->firstItem() ?? 0 }} - {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }}
  </div>
  <div>
    {{ $suppliers->links() }}
  </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-body">
          <h5>Are you sure?</h5>
          <p>This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-danger" type="submit">Delete</button>
        </div>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  document.querySelectorAll('.pick-supplier-btn').forEach(btn=>{
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      const name = this.dataset.name;
      // If opened from a purchase request window using window.opener:
      if (window.opener && typeof window.opener.receivePickedSupplier === 'function') {
        window.opener.receivePickedSupplier({ id, name });
        window.close();
        return;
      }

      // Otherwise try broadcast message to the parent via postMessage (useful if embedded)
      window.postMessage({type: 'supplier-picked', supplier: {id, name}}, '*');
      // optionally show a toast
      alert('Picked supplier: ' + name + '. If you opened this page from a purchase request form, it should pick it automatically.');
    });
  });

  // delete
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', function(){
      document.getElementById('deleteForm').action = this.dataset.action;
      deleteModal.show();
    });
  });
</script>
@endpush
@endsection