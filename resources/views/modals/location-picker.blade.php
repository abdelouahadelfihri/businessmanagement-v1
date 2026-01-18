<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- ADD NEW UNIT --}}
                <h6 class="fw-bold mb-2">Add New Location</h6>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" id="new_location_name" class="form-control" placeholder="Location Name">
                    </div>
                    <div class="col-md-6">
                        <input type="email" id="new_location_abbreviation" class="form-control"
                            placeholder="Abbreviation">
                    </div>
                </div>

                <button type="button" class="btn btn-success w-100" id="addLocationBtn">+ Add Location</button>

                <hr>

                {{-- Search --}}
                <div class="mb-2">
                    <input type="text" id="locationSearch" class="form-control" placeholder="Search locations...">
                </div>

                {{-- Table --}}
                <table class="table table-hover" id="locationTable">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\MasterData\Location::all() as $location)
                            <tr>
                                <td>{{ $location->id }}</td>
                                <td>{{ $location->name }}</td>
                                <td>{{ $location->address }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-location"
                                        data-id="{{ $location->id }}" data-name="{{ $location->name }}"
                                        data-abbreviation="{{ $location->abbreviation }}">
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
            $('#locationSearch').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                $('#locationTable tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // ➕ Add location via AJAX
            $('#addLocationBtn').click(function () {
                let name = $('#new_location_name').val().trim();
                let email = $('#new_location_email').val().trim();
                let phone = $('#new_location_phone').val().trim();
                let address = $('#new_location_address').val().trim();

                if (name === '') return alert('Name is required');

                $.ajax({
                    url: '{{ route("locations.ajaxStore") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: name,
                        email: email,
                        phone: phone,
                        address: address
                    },
                    success: function (data) {
                        $('#locationTable tbody').append(`
                                                                                                                                <tr>
                                                                                                                                    <td>${data.name}</td>
                                                                                                                                    <td>${data.email ?? ''}</td>
                                                                                                                                    <td>${data.phone ?? ''}</td>
                                                                                                                                    <td>
                                                                                                                                        <button type="button" class="btn btn-sm btn-primary select-location"
                                                                                                                                            data-id="${data.id}" data-name="${data.name}" data-email="${data.email}" data-phone="${data.phone}">
                                                                                                                                            Select
                                                                                                                                        </button>
                                                                                                                                    </td>
                                                                                                                                </tr>
                                                                                                                            `);

                        // clear inputs
                        $('#new_location_name').val('');
                        $('#new_location_email').val('');
                        $('#new_location_phone').val('');
                        $('#new_location_address').val('');
                    }
                });
            });

            // 🎯 Select location
            $(document).on('click', '.select-location', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#location_id').val(id);
                $('#location_name').val(name);

                // close modal
                let modalEl = document.getElementById('locationModal');
                let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            });

        });
    </script>
@endpush