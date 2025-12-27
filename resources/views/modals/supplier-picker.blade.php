<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <input type="text" id="supplierSearch" class="form-control" placeholder="Search suppliers...">
                </div>
                <table class="table table-hover" id="supplierTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\MasterData\Supplier::all() as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td><button type="button" class="btn btn-sm btn-primary select-supplier"
                                        data-id="{{ $s->id }}" data-name="{{ $s->name }}">Select</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <hr>
                <h6>Add New Supplier</h6>
                <div class="input-group">
                    <input type="text" id="new_supplier_name" class="form-control" placeholder="Supplier Name">
                    <button type="button" class="btn btn-success" id="addSupplierBtn">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#addSupplierBtn').click(function () {
        let name = $('#new_supplier_name').val();
        if (name) {
            $.post('{{ route('suppliers.ajaxStore') }}', { _token: '{{ csrf_token() }}', name: name }, function (data) {
                // append new supplier to table
                $('#supplierTable tbody').append('<tr><td>' + data.name + '</td><td><button type="button" class="btn btn-sm btn-primary select-supplier" data-id="' + data.id + '" data-name="' + data.name + '">Select</button></td></tr>');
                $('#new_supplier_name').val('');
            });
        }
    });

    $(document).on('click', '.select-supplier', function () {
        let id = $(this).data('id');
        let name = $(this).data('name');

        $('#supplier_id').val(id);
        $('#supplier_name').val(name);
        $('#invoice_supplier_id').val(id);
        $('#invoice_supplier_name').val(name);
        $('#receipt_supplier_id').val(id);
        $('#receipt_supplier_name').val(name);

        // Bootstrap 5 modal hide
        let supplierModal = document.getElementById('supplierModal');
        let modal = bootstrap.Modal.getInstance(supplierModal);
        modal.hide();
    });

</script>