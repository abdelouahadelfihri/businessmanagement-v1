{{-- Contacts card for an owner (customer / supplier / prospect). Expects $owner with contacts loaded. --}}
@php
    $ownerKey = $owner->getMorphClass() . ':' . $owner->getKey();
    $readOnly = $readOnly ?? false;
@endphp

<div class="card shadow-sm mb-3" id="contacts">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-lines-fill"></i> Contacts <span class="badge bg-light text-dark ms-1">{{ $owner->contacts->count() }}</span></span>
        @unless($readOnly)
            @canroute('crm.contacts.create')
            <a href="{{ route('crm.contacts.create', ['owner' => $ownerKey, 'return' => 'owner']) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person-plus"></i> Add contact
            </a>
            @endcanroute
        @endunless
    </div>

    @if($owner->contacts->isEmpty())
        <div class="card-body text-muted small">
            No contacts yet.
            @unless($readOnly)
                @canroute('crm.contacts.create')
                <a href="{{ route('crm.contacts.create', ['owner' => $ownerKey, 'return' => 'owner']) }}">Add the first one</a>
                @else
                Add the first one
                @endcanroute
                — it becomes the primary contact.
            @endunless
        </div>
    @else
        <div class="row g-0">
            @foreach($owner->contacts as $person)
                <div class="col-md-6 col-xl-4 border-bottom border-end-md">
                    <div class="p-3 h-100 d-flex gap-3 {{ $person->is_active ? '' : 'opacity-50' }}">
                        <span class="contact-avatar">{{ $person->initials }}</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @canroute('crm.contacts.show')
                                <a href="{{ route('crm.contacts.show', $person) }}" class="fw-semibold text-decoration-none">{{ $person->display_name }}</a>
                                @else
                                {{ $person->display_name }}
                                @endcanroute
                                @if($person->is_primary)
                                    <span class="badge bg-primary-subtle text-primary">Primary</span>
                                @endif
                                @unless($person->is_active)
                                    <span class="badge bg-secondary">Inactive</span>
                                @endunless
                            </div>
                            <small class="text-muted d-block">
                                {{ collect([$person->job_title, $person->department])->filter()->implode(' · ') ?: '—' }}
                            </small>

                            <div class="small mt-2 text-break">
                                @if($person->email)
                                    <div><i class="bi bi-envelope text-muted"></i> <a href="mailto:{{ $person->email }}">{{ $person->email }}</a></div>
                                @endif
                                @if($person->phone)
                                    <div><i class="bi bi-telephone text-muted"></i> <a href="tel:{{ $person->phone }}">{{ $person->phone }}</a></div>
                                @endif
                                @if($person->mobile)
                                    <div><i class="bi bi-phone text-muted"></i> <a href="tel:{{ $person->mobile }}">{{ $person->mobile }}</a></div>
                                @endif
                            </div>

                            @unless($readOnly)
                                <div class="d-flex flex-wrap gap-1 mt-2">
                                    @if($person->whatsapp_url)
                                        <a href="{{ $person->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                    @canroute('crm.contacts.edit')
                                    <a href="{{ route('crm.contacts.edit', [$person, 'return' => 'owner']) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endcanroute
                                    @unless($person->is_primary)
                                        @canroute('crm.contacts.primary')
                                        <form action="{{ route('crm.contacts.primary', $person) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Make primary">
                                                <i class="bi bi-star"></i>
                                            </button>
                                        </form>
                                        @endcanroute
                                    @endunless
                                    @canroute('crm.contacts.destroy')
                                    <form action="{{ route('crm.contacts.destroy', $person) }}" method="POST"
                                        onsubmit="return confirm('Delete this contact?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="return" value="owner">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcanroute
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
