@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Create Purchase Request</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                <form action="{{ route('purchasesrequests.store') }}" method="POST">
                    @csrf

                    {{-- Supplier --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <div class="input-group">
                            <input type="text" id="supplier_name_display" class="form-control"
                                placeholder="Pick supplier..." readonly>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#modalSupplierPicker">
                                Pick
                            </button>
                        </div>
                        <input type="hidden" name="supplier_id" id="supplier_id_input">
                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Request Date --}}
                    <div class="mb-3">
                        <label class="form-label">Request Date</label>
                        <input type="date" name="request_date" class="form-control" required>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">-- choose --</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>

                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('purchasesrequests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    @include('components.modal-supplier-picker') {{-- include modal --}}
@endsection