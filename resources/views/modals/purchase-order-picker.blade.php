<div class="modal fade" id="purchaseOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select A Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Search --}}
                <div class="mb-2">
                    <input type="text" id="purchasesOrdersSearch" class="form-control"
                        placeholder="Search purchases orders...">
                </div>

                {{-- Table --}}
                <table class="table table-hover" id="purchasesOrdersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\Purchases\PurchaseOrder::all() as $po)
                            <tr>
                                <td>{{ $po->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>{{ $s->phone }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary purchase-request"
                                        data-id="{{ $s->id }}" data-name="{{ $s->name }}" data-email="{{ $s->email }}"
                                        data-phone="{{ $s->phone }}">
                                        Select
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {

            // 🔎 Search filter
            $('#purchasesRequestsSearch').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                $('#purchasesRequestsTable tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // 🎯 Select purchase request

            $(document).on('click', '.select-purchase-request', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#purchase_request_id').val(id);
                $('#purchase_request_name').val(name);

                // close modal
                let modalEl = document.getElementById('supplierModal');
                let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            });

        });
    </script>
@endpush