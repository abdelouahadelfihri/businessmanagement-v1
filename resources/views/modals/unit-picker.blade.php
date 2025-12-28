<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Search --}}
                <div class="mb-2">
                    <input type="text" id="unitSearch" class="form-control" placeholder="Search units...">
                </div>

                {{-- Table --}}
                <table class="table table-hover" id="unitTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\MasterData\Unit::all() as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>{{ $s->phone }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-unit"
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

                {{-- ADD NEW UNIT --}}
                <h6 class="fw-bold mb-2">Add New Unit</h6>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_unit_name" class="form-control" placeholder="Unit Name">
                    </div>
                    <div class="col-md-6">
                        <input type="email" id="new_unit_email" class="form-control" placeholder="Email">
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_unit_phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="new_unit_address" class="form-control" placeholder="Address">
                    </div>
                </div>

                <button type="button" class="btn btn-success w-100" id="addUnitBtn">+ Add Unit</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {

            // 🔎 Search filter
            $('#unitSearch').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                $('#unitTable tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // ➕ Add unit via AJAX
            $('#addUnitBtn').click(function () {
                let name = $('#new_unit_name').val().trim();
                let email = $('#new_unit_email').val().trim();
                let phone = $('#new_unit_phone').val().trim();
                let address = $('#new_unit_address').val().trim();

                if (name === '') return alert('Name is required');

                $.ajax({
                    url: '{{ route("units.ajaxStore") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: name,
                        email: email,
                        phone: phone,
                        address: address
                    },
                    success: function (data) {
                        $('#unitTable tbody').append(`
                                                    <tr>
                                                        <td>${data.name}</td>
                                                        <td>${data.email ?? ''}</td>
                                                        <td>${data.phone ?? ''}</td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-primary select-unit"
                                                                data-id="${data.id}" data-name="${data.name}" data-email="${data.email}" data-phone="${data.phone}">
                                                                Select
                                                            </button>
                                                        </td>
                                                    </tr>
                                                `);

                        // clear inputs
                        $('#new_unit_name').val('');
                        $('#new_unit_email').val('');
                        $('#new_unit_phone').val('');
                        $('#new_unit_address').val('');
                    }
                });
            });

            // 🎯 Select unit
            $(document).on('click', '.select-unit', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#unit_id').val(id);
                $('#unit_name').val(name);

                // close modal
                let modalEl = document.getElementById('unitModal');
                let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            });

        });
    </script>
@endpush