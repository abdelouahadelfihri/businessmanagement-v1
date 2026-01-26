<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Select Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->name }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="selectProduct('{{ $product->id }}','{{ $product->name }}')">
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
        let lineCount = 0;

        document.getElementById('add-line').addEventListener('click', function () {
            $('#productModal').modal('show');
        });

        $(document).on('click', '.select-product', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const row = `
            <tr>
                <td>
                    <input type="hidden" name="lines[${lineCount}][product_id]" value="${id}">
                    ${name}
                </td>
                <td><input type="number" name="lines[${lineCount}][quantity]" class="form-control" min="1" required></td>
                <td><button type="button" class="btn btn-danger remove-line">Remove</button></td>
            </tr>`;
            $('#lines-table tbody').append(row);
            lineCount++;
            $('#productModal').modal('hide');
        });

        $(document).on('click', '.remove-line', function () {
            $(this).closest('tr').remove();
        });
    </script>
@endpush