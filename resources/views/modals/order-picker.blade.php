<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <input type="text" id="orderSearch" class="form-control" placeholder="Search orders...">
                </div>
                <table class="table table-hover" id="orderTable">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Supplier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX loaded orders here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function loadOrdersForSupplier(supplier_id) {
        $.get('/orders/by-supplier/' + supplier_id, function (data) {
            let tbody = '';
            data.forEach(function (order) {
                tbody += '<tr>' +
                    '<td>' + order.order_number + '</td>' +
                    '<td>' + order.supplier_name + '</td>' +
                    '<td><button type="button" class="btn btn-sm btn-primary select-order" data-id="' + order.id + '" data-number="' + order.order_number + '">Select</button></td>' +
                    '</tr>';
            });
            $('#orderTable tbody').html(tbody);
        });
    }

    $(document).on('click', '.select-order', function () {
        let id = $(this).data('id');
        let number = $(this).data('number');
        $('#receipt_order_id').val(id);
        $('#receipt_order_number').val(number);
        $('#invoice_order_id').val(id);
        $('#invoice_order_number').val(number);
        $('#orderModal').modal('hide');
    });

    $('#orderModal').on('show.bs.modal', function () {
        let supplier_id = $('#receipt_supplier_id').val() || $('#invoice_supplier_id').val();
        if (supplier_id) {
            loadOrdersForSupplier(supplier_id);
        } else {
            $('#orderTable tbody').html('<tr><td colspan="3">Please select a supplier first.</td></tr>');
        }
    });
</script>