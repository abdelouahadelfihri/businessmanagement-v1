@extends('layouts.app')

@section('title', $contact->full_name)

@section('content')
    @php
        $ct = $contact;
        $meta = $ct->owner_meta;
        $owner = $ct->contactable;
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0 d-flex align-items-center gap-3">
                <span class="contact-avatar contact-avatar-lg">{{ $ct->initials }}</span>
                <span>
                    {{ $ct->display_name }}
                    @if($ct->is_primary)
                        <span class="badge bg-primary fs-6 align-middle">Primary</span>
                    @endif
                    <span class="badge bg-{{ $ct->is_active ? 'success' : 'secondary' }} fs-6 align-middle">
                        {{ $ct->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <small class="d-block fs-6 text-muted mt-1">
                        {{ collect([$ct->job_title, $ct->department])->filter()->implode(' · ') ?: 'No job title' }}
                    </small>
                </span>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @if($ct->email)
                    <a href="mailto:{{ $ct->email }}" class="btn btn-outline-primary"><i class="bi bi-envelope"></i> Email</a>
                @endif
                @if($ct->mobile || $ct->phone)
                    <a href="tel:{{ $ct->mobile ?: $ct->phone }}" class="btn btn-outline-primary"><i class="bi bi-telephone"></i> Call</a>
                @endif
                @if($ct->whatsapp_url)
                    <a href="{{ $ct->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-outline-success"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                @endif
                <a href="{{ route('crm.contacts.edit', $ct) }}" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
                <form action="{{ route('crm.contacts.destroy', $ct) }}" method="POST" onsubmit="return confirm('Delete this contact?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                </form>
                <a href="{{ route('crm.contacts.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Contact details</div>
                    <div class="card-body row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Email</small>
                            @if($ct->email)<a href="mailto:{{ $ct->email }}" class="text-break">{{ $ct->email }}</a>@else — @endif
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Preferred channel</small>
                            {{ \App\Models\CRM\Contact::CHANNELS[$ct->preferred_channel] ?? '—' }}
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Phone</small>
                            @if($ct->phone)<a href="tel:{{ $ct->phone }}">{{ $ct->phone }}</a>@else — @endif
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Mobile</small>
                            @if($ct->mobile)<a href="tel:{{ $ct->mobile }}">{{ $ct->mobile }}</a>@else — @endif
                        </div>
                        <div class="col-sm-6"><small class="text-muted d-block">Added on</small>{{ optional($ct->created_at)->format('Y-m-d') }}</div>
                        <div class="col-sm-6"><small class="text-muted d-block">Last update</small>{{ optional($ct->updated_at)->format('Y-m-d H:i') }}</div>
                        <div class="col-12"><small class="text-muted d-block">Notes</small>{!! nl2br(e($ct->notes ?: '—')) !!}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Company</div>
                    <div class="card-body">
                        @if($owner)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-{{ $meta['color'] }}"><i class="bi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}</span>
                                <a href="{{ $ct->owner_url }}" class="fw-semibold fs-5 text-decoration-none">{{ $owner->name }}</a>
                            </div>
                            <small class="text-muted d-block mb-3">{{ $owner->code }}{{ $owner->city ? ' · ' . $owner->city : '' }}</small>

                            <h6 class="fw-semibold mt-3">Other contacts at {{ $owner->name }}</h6>
                            <div class="list-group list-group-flush">
                                @forelse($colleagues as $other)
                                    <a href="{{ route('crm.contacts.show', $other) }}" class="list-group-item list-group-item-action px-0 d-flex align-items-center gap-2">
                                        <span class="contact-avatar contact-avatar-sm">{{ $other->initials }}</span>
                                        <span class="flex-grow-1">
                                            {{ $other->full_name }}
                                            <small class="d-block text-muted">{{ $other->job_title ?? '—' }}</small>
                                        </span>
                                        @if($other->is_primary)<span class="badge bg-primary-subtle text-primary">Primary</span>@endif
                                    </a>
                                @empty
                                    <div class="text-muted small">No other contacts.</div>
                                @endforelse
                            </div>
                        @else
                            <span class="text-muted">The company of this contact no longer exists.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
