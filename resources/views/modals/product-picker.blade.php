<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Select Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- Search --}}

                <div class="mb-2">
                    <input type="text" id="productSearch" class="form-control" placeholder="Search suppliers...">
                </div>

                {{-- Table --}}

                <table class="table table-hover" id="productsTable">
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
                                    <button type="button" class="btn btn-sm btn-primary select-product"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}">
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
            $('#productSearch').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                $('#productsTable tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endpush