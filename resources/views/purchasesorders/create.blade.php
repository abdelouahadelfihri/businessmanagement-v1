@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Create Purchase Order</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ================= SUPPLIER PICKER ================= --}}
                <form id="supplierPickerForm" method="GET" action="{{ route('suppliers.index') }}">
                    <input type="hidden" name="select_for" value="purchase-order">
                    <input type="hidden" name="return_url" value="{{ route('purchaseorders.create') }}">

                    {{-- preserve form fields --}}
                    @foreach(['order_date', 'status'] as $field)
                        <input type="hidden" id="hidden_{{ $field }}" name="{{ $field }}"
                            value="{{ old($field, $form[$field] ?? '') }}">
                    @endforeach
                </form>

                {{-- ================= REQUEST PICKER ================= --}}
                <form id="requestPickerForm" method="GET" action="{{ route('purchasesrequests.index') }}">
                    <input type="hidden" name="select_for" value="purchase-order">
                    <input type="hidden" name="return_url" value="{{ route('purchaseorders.create') }}">

                    {{-- preserve form fields --}}
                    @foreach(['order_date', 'status'] as $field)
                        <input type="hidden" id="hidden2_{{ $field }}" name="{{ $field }}"
                            value="{{ old($field, $form[$field] ?? '') }}">
                    @endforeach

                    @if(!empty($selectedSupplier))
                        <input type="hidden" name="selected_supplier_id" value="{{ $selectedSupplier->id }}">
                    @endif
                </form>

                {{-- ================= MAIN FORM ================= --}}
                <form action="{{ route('purchaseorders.store') }}" method="POST">
                    @csrf

                    {{-- SUPPLIER --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <div class="input-group">
                            <input class="form-control" value="{{ $selectedSupplier?->name }}" readonly
                                placeholder="No supplier selected">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="submitSupplierPicker()">Pick</button>
                        </div>
                        <input type="hidden" name="supplier_id" value="{{ $selectedSupplier?->id }}">
                    </div>

                    {{-- PURCHASE REQUEST --}}
                    <div class="mb-3">
                        <label class="form-label">Linked Purchase Request (optional)</label>
                        <div class="input-group">
                            <input class="form-control" value="{{ $selectedRequest?->title }}" readonly
                                placeholder="None selected">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="submitRequestPicker()">Pick</button>
                        </div>
                        <input type="hidden" name="request_id" value="{{ $selectedRequest?->id }}">
                    </div>

                    {{-- ORDER DATE --}}
                    <div class="mb-3">
                        <label class="form-label">Order Date</label>
                        <input type="date" id="order_date" name="order_date" required class="form-control"
                            value="{{ old('order_date', $form['order_date'] ?? '') }}">
                    </div>

                    {{-- STATUS --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">-- Select --</option>
                            @foreach(['draft', 'sent', 'partially_received', 'completed', 'cancelled'] as $s)
                                <option value="{{ $s }}" @selected(($form['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('purchaseorders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function submitSupplierPicker() {
            ['order_date', 'status'].forEach(f => {
                document.getElementById('hidden_' + f).value =
                    document.getElementById(f)?.value;
            });
            document.getElementById('supplierPickerForm').submit();
        }
        function submitRequestPicker() {
            ['order_date', 'status'].forEach(f => {
                document.getElementById('hidden2_' + f).value =
                    document.getElementById(f)?.value;
            });
            document.getElementById('requestPickerForm').submit();
        }
    </script>
@endpush