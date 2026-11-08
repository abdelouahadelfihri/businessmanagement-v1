@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
    @php $types = \App\Models\CRM\Contact::OWNER_TYPES; @endphp

    <div class="container mt-4">
        <h1 class="mb-4">List of Contacts</h1>

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('crm.contacts.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Contact
            </a>
            @foreach($types as $key => $meta)
                <a href="{{ route('crm.contacts.index', ['owner_type' => $key]) }}"
                    class="btn btn-sm rounded-pill {{ request('owner_type') === $key ? 'btn-dark' : 'btn-outline-secondary' }}">
                    <i class="bi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}s
                    <span class="badge bg-light text-dark ms-1">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Filter & search form --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('crm.contacts.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, company, email, phone or job title..." value="{{ request('search') }}">
                    </div>

                    <div class="col-6 col-md-2">
                        <label for="owner_type" class="form-label mb-1">Company type</label>
                        <select name="owner_type" id="owner_type" class="form-select">
                            <option value="">All</option>
                            @foreach($types as $key => $meta)
                                <option value="{{ $key }}" @selected(request('owner_type') === $key)>{{ $meta['label'] }}s</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('crm.contacts.index') }}" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="primary" value="1" id="primary"
                                @checked(request()->boolean('primary')) onchange="this.form.submit()">
                            <label class="form-check-label" for="primary">Primary contacts only</label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($contacts->isEmpty())
            <div class="alert alert-info">No contacts found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="contactsTable"
                            class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Job title</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $contact)
                                    @php $meta = $contact->owner_meta; @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('crm.contacts.show', $contact) }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
                                                <span class="contact-avatar contact-avatar-sm">{{ $contact->initials }}</span>
                                                <span class="fw-semibold">{{ $contact->display_name }}</span>
                                            </a>
                                            @if($contact->is_primary)
                                                <span class="badge bg-primary-subtle text-primary ms-1">Primary</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $meta['color'] }} me-1"><i class="bi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}</span>
                                            @if($contact->contactable)
                                                <a href="{{ $contact->owner_url }}">{{ $contact->contactable->name }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $contact->job_title ?? '—' }}</td>
                                        <td>
                                            @if($contact->email)<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@else — @endif
                                        </td>
                                        <td>{{ $contact->mobile ?? $contact->phone ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $contact->is_active ? 'success' : 'secondary' }}">
                                                {{ $contact->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('crm.contacts.show', $contact) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('crm.contacts.edit', $contact) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                @if($contact->whatsapp_url)
                                                    <a href="{{ $contact->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-success" title="WhatsApp">
                                                        <i class="bi bi-whatsapp"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('crm.contacts.destroy', $contact) }}" method="POST"
                                                    onsubmit="return confirm('Delete this contact?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        Showing {{ $contacts->firstItem() }} to {{ $contacts->lastItem() }} of {{ $contacts->total() }} contacts
                    </small>
                    {{ $contacts->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#contactsTable').DataTable({
                paging: false, searching: false, info: false, ordering: true, order: [], autoWidth: false,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
