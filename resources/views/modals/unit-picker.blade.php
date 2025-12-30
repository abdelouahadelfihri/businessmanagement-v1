<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- ADD NEW UNIT --}}
                <h6 class="fw-bold mb-2">Add New Unit</h6>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_unit_name" class="form-control" placeholder="Unit Name">
                    </div>
                    <div class="col-md-6">
                        <input type="email" id="new_unit_abbreviation" class="form-control" placeholder="Abbreviation">
                    </div>
                </div>

                <button type="button" class="btn btn-success w-100" id="addUnitBtn">+ Add Unit</button>

                <hr>

                {{-- Search --}}
                <div class="mb-2">
                    <input type="text" id="unitSearch" class="form-control" placeholder="Search units...">
                </div>

                {{-- Table --}}
                <table class="table table-hover" id="unitTable">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Abbreviation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\MasterData\Unit::all() as $unit)
                            <tr>
                                <td>{{ $unit->id }}</td>
                                <td>{{ $unit->name }}</td>
                                <td>{{ $unit->abbreviation }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-unit"
                                        data-id="{{ $unit->id }}" data-name="{{ $unit->name }}"
                                        data-abbreviation="{{ $unit->abbreviation }}">
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