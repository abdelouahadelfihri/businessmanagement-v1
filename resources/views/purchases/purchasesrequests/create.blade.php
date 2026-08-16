@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Create Purchase Request</h3>
            <a href="{{ route('purchases.purchasesrequests.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('purchases.purchasesrequests.store') }}" enctype="multipart/form-data"
                    id="prForm">
                    @csrf

                    <!-- Supplier -->
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <div class="input-group">
                            <input type="hidden" name="supplier_id" id="supplier_id" value="{{ old('supplier_id') }}">
                            <input type="text" id="supplier_name" class="form-control" readonly>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#supplierModal">
                                Select Supplier
                            </button>
                        </div>
                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <!-- Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                        </div>

                        <!-- Expected Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Date</label>
                            <input type="date" name="expected_date" class="form-control" value="{{ old('expected_date') }}">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Priority -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" required>
                                <option value="low" @selected(old('priority') == 'low')>Low</option>
                                <option value="medium" @selected(old('priority', 'medium') == 'medium')>Medium</option>
                                <option value="high" @selected(old('priority') == 'high')>High</option>
                                <option value="urgent" @selected(old('priority') == 'urgent')>Urgent</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="draft" @selected(old('status', 'draft') == 'draft')>Draft</option>
                                <option value="pending" @selected(old('status') == 'pending')>Pending</option>
                                <option value="approved" @selected(old('status') == 'approved')>Approved</option>
                                <option value="rejected" @selected(old('status') == 'rejected')>Rejected</option>
                                <option value="ordered" @selected(old('status') == 'ordered')>Ordered</option>
                                <option value="completed" @selected(old('status') == 'completed')>Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Currency (total_amount removed — now computed from lines) -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" maxlength="3"
                                value="{{ old('currency', 'MAD') }}">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Line Items -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-bold">Line Items</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn">
                                <i class="bi bi-plus-lg"></i> Add Line
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="linesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:200px;">Product</th>
                                        <th style="min-width:180px;">Description</th>
                                        <th style="width:100px;">Unit</th>
                                        <th style="width:110px;">Quantity</th>
                                        <th style="width:130px;">Unit Price</th>
                                        <th style="width:130px;">Total</th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="linesBody">
                                    <!-- rows injected by JS -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Grand Total</td>
                                        <td class="fw-bold" id="grandTotalDisplay">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @error('lines')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Attachment -->
                    <div class="mb-3">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('purchases.purchasesrequests.index') }}" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('modals.supplier-picker')
@endsection

@push('scripts')
    <script>
        (function () {
            const products = @json($products ?? []);

            let rowIndex = 0;
            const linesBody = document.getElementById('linesBody');
            const grandTotalDisplay = document.getElementById('grandTotalDisplay');

            function buildProductOptions(selectedId) {
                let options = '<option value="">-- Free text --</option>';
                products.forEach(p => {
                    const selected = (selectedId && String(selectedId) === String(p.id)) ? 'selected' : '';
                    options += `<option value="${p.id}" ${selected}>${p.name}</option>`;
                });
                return options;
            }

            function addRow() {
                const tr = document.createElement('tr');
                tr.dataset.index = rowIndex;

                tr.innerHTML = `
                    <td>
                        <select name="lines[${rowIndex}][product_id]" class="form-select product-select">
                            ${buildProductOptions()}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="lines[${rowIndex}][description]" class="form-control">
                    </td>
                    <td>
                        <input type="text" name="lines[${rowIndex}][unit]" class="form-control" placeholder="pcs">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="lines[${rowIndex}][quantity]" class="form-control qty-input" value="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="lines[${rowIndex}][unit_price]" class="form-control price-input" value="0" required>
                    </td>
                    <td>
                        <input type="text" class="form-control line-total-display" value="0.00" readonly tabindex="-1">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-line-btn">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                linesBody.appendChild(tr);
                rowIndex++;
                recalcAll();
            }

            function recalcRow(tr) {
                const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
                const price = parseFloat(tr.querySelector('.price-input').value) || 0;
                const total = qty * price;
                tr.querySelector('.line-total-display').value = total.toFixed(2);
                return total;
            }

            function recalcAll() {
                let grandTotal = 0;
                linesBody.querySelectorAll('tr').forEach(tr => {
                    grandTotal += recalcRow(tr);
                });
                grandTotalDisplay.textContent = grandTotal.toFixed(2);
            }

            document.getElementById('addLineBtn').addEventListener('click', addRow);

            linesBody.addEventListener('input', function (e) {
                if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
                    recalcAll();
                }
            });

            linesBody.addEventListener('change', function (e) {
                if (e.target.classList.contains('product-select')) {
                    const selected = e.target.options[e.target.selectedIndex];
                    const price = selected.getAttribute('data-price');
                    if (price) {
                        const tr = e.target.closest('tr');
                        tr.querySelector('.price-input').value = parseFloat(price).toFixed(2);
                        recalcAll();
                    }
                }
            });

            linesBody.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-line-btn');
                if (btn) {
                    btn.closest('tr').remove();
                    recalcAll();
                }
            });

            // Start with one empty row
            addRow();
        })();
    </script>
@endpush