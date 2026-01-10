<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Search --}}
                <div class="mb-2">
                    <input type="text" id="categorySearch" class="form-control" placeholder="Search categories...">
                </div>

                {{-- Table --}}
                <table class="table table-hover" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\MasterData\Category::all() as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>{{ $s->phone }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-category"
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
                <h6 class="fw-bold mb-2">Add New Category</h6>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_category_name" class="form-control" placeholder="Category Name">
                    </div>
                    <div class="col-md-6">
                        <input type="email" id="new_category_email" class="form-control" placeholder="Email">
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_category_phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="new_category_address" class="form-control" placeholder="Address">
                    </div>
                </div>

                <button type="button" class="btn btn-success w-100" id="addCategoryBtn">+ Add Category</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {

            // 🔎 Search filter
            $('#categorySearch').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                $('#categoryTable tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // ➕ Add category via AJAX
            $('#addCategoryBtn').click(function () {
                let name = $('#new_category_name').val().trim();
                let email = $('#new_category_email').val().trim();
                let phone = $('#new_category_phone').val().trim();
                let address = $('#new_category_address').val().trim();

                if (name === '') return alert('Name is required');

                $.ajax({
                    url: '{{ route("categories.ajaxStore") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: name,
                        email: email,
                        phone: phone,
                        address: address
                    },
                    success: function (data) {
                        $('#categoryTable tbody').append(`
                                        <tr>
                                            <td>${data.name}</td>
                                            <td>${data.email ?? ''}</td>
                                            <td>${data.phone ?? ''}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary select-category"
                                                    data-id="${data.id}" data-name="${data.name}" data-email="${data.email}" data-phone="${data.phone}">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                    `);

                        // clear inputs
                        $('#new_category_name').val('');
                        $('#new_category_email').val('');
                        $('#new_category_phone').val('');
                        $('#new_category_address').val('');
                    }
                });
            });

            // 🎯 Select category
            $(document).on('click', '.select-category', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#category_id').val(id);
                $('#category_name').val(name);

                // close modal
                let modalEl = document.getElementById('categoryModal');
                let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            });

        });
    </script>
@endpush