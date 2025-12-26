<div class="modal fade" id="modalSupplierPicker" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Pick Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Search --}}
                <input type="text" id="supplierSearchInput" class="form-control mb-2" placeholder="Search...">

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="suppliersTableModal">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                {{-- Add New Supplier --}}
                <hr>
                <h6>Add New Supplier</h6>
                <form id="ajaxCreateSupplierForm">
                    @csrf
                    <input type="text" name="name" class="form-control mb-2" placeholder="Supplier name" required>
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email">
                    <input type="text" name="phone" class="form-control mb-2" placeholder="Phone">
                    <button type="submit" class="btn btn-success btn-sm">+ Add</button>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        let searchTimeout;

        function loadSuppliers(search = "") {
            fetch("{{ route('suppliers.ajax.list') }}?search=" + search)
                .then(r => r.json())
                .then(json => {
                    let html = "";
                    json.data.data.forEach(s => {
                        html += `
                    <tr>
                        <td>${s.name}</td>
                        <td>${s.email ?? ''}</td>
                        <td>${s.phone ?? ''}</td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                onclick="selectSupplier(${s.id}, '${s.name.replace(/'/g, "")}')">
                                Select
                            </button>
                        </td>
                    </tr>`;
                    });
                    document.querySelector("#suppliersTableModal tbody").innerHTML = html;
                });
        }

        function selectSupplier(id, name) {
            document.getElementById("supplier_id_input").value = id;
            document.getElementById("supplier_name_display").value = name;
            bootstrap.Modal.getInstance(document.getElementById('modalSupplierPicker')).hide();
        }

        document.getElementById("supplierSearchInput").addEventListener("input", e => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadSuppliers(e.target.value), 300);
        });

        document.getElementById("ajaxCreateSupplierForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let form = new FormData(this);

            fetch("{{ route('suppliers.ajax.create') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: form
            })
                .then(r => r.json())
                .then(json => {
                    if (json.success) {
                        loadSuppliers();
                        this.reset();
                    }
                });
        });

        // Load when modal opened
        document.getElementById('modalSupplierPicker').addEventListener('shown.bs.modal', () => loadSuppliers());
    </script>
@endpush