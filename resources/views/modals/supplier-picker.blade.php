<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Search --}}
                <div class="mb-2">
                    <input type="text" id="supplierSearch" class="form-control" placeholder="Search suppliers...">
                </div>

                {{-- Table --}}
                <table class="table table-hover" id="supplierTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\MasterData\Supplier::all() as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>{{ $s->phone }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-supplier"
                                        data-id="{{ $s->id }}" data-name="{{ $s->name }}" data-email="{{ $s->email }}"
                                        data-phone="{{ $s->phone }}">
                                        Select
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr>

                {{-- ADD NEW SUPPLIER --}}
                <h6 class="fw-bold mb-2">Add New Supplier</h6>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_supplier_name" class="form-control" placeholder="Supplier Name">
                    </div>
                    <div class="col-md-6">
                        <input type="email" id="new_supplier_email" class="form-control" placeholder="Email">
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_supplier_phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="new_supplier_address" class="form-control" placeholder="Address">
                    </div>
                </div>

                <button type="button" class="btn btn-success w-100" id="addSupplierBtn">+ Add Supplier</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {

            // 🔎 Search filter
            $('#supplierSearch').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                $('#supplierTable tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // ➕ Add supplier via AJAX
            $('#addSupplierBtn').click(function () {
                let name = $('#new_supplier_name').val().trim();
                let email = $('#new_supplier_email').val().trim();
                let phone = $('#new_supplier_phone').val().trim();
                let address = $('#new_supplier_address').val().trim();

                if (name === '') return alert('Name is required');

                $.ajax({
                    url: '{{ route("suppliers.ajaxStore") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: name,
                        email: email,
                        phone: phone,
                        address: address
                    },
                    success: function (data) {
                        $('#supplierTable tbody').append(`


                        <tr>
                            <td>${data.name}</td>
                            <td>${data.email ?? ''}</td>
                            <td>${data.phone ?? ''}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary select-supplier"
                                    data-id="${data.id}" data-name="${data.name}" data-email="${data.email}" data-phone="${data.phone}">
                                    Select
                                </button>
                            </td>
                        </tr>
                    `);
                    // clear inputs
                    $('#new_supplier_name').val('');
                    $('#new_supplier_email').val('');
                    $('#new_supplier_phone').val('');
                    $('#new_supplier_address').val('');
                    }
                });
            });

            // 🎯 Select supplier
            $(document).on('click', '.select-supplier', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#supplier_id').val(id);
                $('#supplier_name').val(name);

                // close modal
                let modalEl = document.getElementById('supplierModal');
                let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            });

        });
    </script>
@endpush