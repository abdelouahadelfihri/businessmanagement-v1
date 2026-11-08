@extends('layouts.app')

@section('title', $prospect->name)

@section('content')
    @php
        $pr = $prospect;
        $P = \App\Models\CRM\Prospect::class;
        $money = fn($v) => number_format((float) $v, 2);
        $stages = array_keys(array_diff_key($P::STATUSES, ['lost' => 1]));
        $currentIndex = array_search($pr->status, $stages, true);
        $locked = $pr->isConverted();
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0">
                {{ $pr->name }}
                <span class="badge bg-{{ $P::STATUS_COLORS[$pr->status] }} fs-6 align-middle">{{ $P::STATUSES[$pr->status] }}</span>
                <small class="d-block fs-6 text-muted mt-1">
                    {{ $pr->code }} · {{ $P::TYPES[$pr->type] ?? '' }}{{ $pr->industry ? ' · ' . $pr->industry : '' }}
                </small>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @if(! $locked && $pr->status !== 'lost')
                    <form action="{{ route('crm.prospects.convert', $pr) }}" method="POST"
                        onsubmit="return confirm('Create a customer from this prospect and move its contacts?');">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="bi bi-person-check"></i> Convert to customer</button>
                    </form>
                @endif
                <a href="{{ route('crm.prospects.edit', $pr) }}" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
                <form action="{{ route('crm.prospects.destroy', $pr) }}" method="POST"
                    onsubmit="return confirm('Delete this prospect and its contacts?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                </form>
                <a href="{{ route('crm.prospects.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        @if($locked)
            <div class="alert alert-success">
                <i class="bi bi-person-check-fill"></i>
                Converted on {{ optional($pr->converted_at)->format('Y-m-d') }} into customer
                @if($pr->convertedCustomer)
                    <a href="{{ route('masterdata.customers.show', $pr->convertedCustomer) }}" class="alert-link">
                        {{ $pr->convertedCustomer->name }} ({{ $pr->convertedCustomer->code }})
                    </a>.
                @else
                    (since deleted).
                @endif
                Its contacts now belong to the customer.
            </div>
        @elseif($pr->status === 'lost')
            <div class="alert alert-danger"><i class="bi bi-x-octagon"></i> Lost — {{ $pr->lost_reason ?: 'no reason given' }}.</div>
        @elseif($pr->follow_up_overdue)
            <div class="alert alert-warning">
                <i class="bi bi-alarm-fill"></i> Follow-up was due on {{ $pr->next_follow_up_date->format('Y-m-d') }}.
            </div>
        @endif

        {{-- Pipeline stages --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="pipeline">
                    @foreach($stages as $i => $stage)
                        @php
                            $done = $pr->status !== 'lost' && $currentIndex !== false && $i < $currentIndex;
                            $current = $pr->status === $stage;
                        @endphp
                        <form action="{{ route('crm.prospects.status', $pr) }}" method="POST" class="pipeline-step">
                            @csrf
                            <input type="hidden" name="status" value="{{ $stage }}">
                            <button type="submit" @disabled($locked || $current)
                                class="btn w-100 {{ $current ? 'btn-' . $P::STATUS_COLORS[$stage] : ($done ? 'btn-success-subtle' : 'btn-light') }}">
                                @if($done)<i class="bi bi-check2"></i>@endif
                                {{ $P::STATUSES[$stage] }}
                            </button>
                        </form>
                    @endforeach
                </div>

                @unless($locked || $pr->status === 'lost')
                    <form action="{{ route('crm.prospects.status', $pr) }}" method="POST" class="row g-2 align-items-end mt-2">
                        @csrf
                        <input type="hidden" name="status" value="lost">
                        <div class="col-md-9">
                            <label class="form-label small mb-1 text-muted">Mark as lost</label>
                            <input type="text" name="lost_reason" maxlength="255" class="form-control form-control-sm"
                                placeholder="Reason, e.g. went with a competitor" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-x-circle"></i> Mark as lost</button>
                        </div>
                    </form>
                @endunless

                @if($pr->status === 'lost' && ! $locked)
                    <small class="text-muted d-block mt-2">Click a stage to reopen this prospect.</small>
                @endif
            </div>
        </div>

        {{-- Key figures --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Estimated value', $money($pr->estimated_value) . ' ' . $pr->currency, 'bi-cash-stack', 'primary'],
                ['Probability', $pr->probability . ' %', 'bi-percent', 'info'],
                ['Weighted value', $money($pr->weighted_value) . ' ' . $pr->currency, 'bi-graph-up-arrow', 'success'],
                ['Expected close', optional($pr->expected_close_date)->format('Y-m-d') ?? '—', 'bi-calendar-check', 'warning'],
            ] as [$title, $value, $icon, $tone])
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1"><i class="bi {{ $icon }} text-{{ $tone }}"></i> {{ $title }}</small>
                            <div class="fs-5 fw-bold text-break">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Email</small>
                    @if($pr->email)<a href="mailto:{{ $pr->email }}">{{ $pr->email }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Phone</small>
                    @if($pr->phone)<a href="tel:{{ $pr->phone }}">{{ $pr->phone }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Website</small>
                    @if($pr->website)<a href="{{ $pr->website }}" target="_blank" rel="noopener">{{ $pr->website }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4"><small class="text-muted d-block">Source</small>{{ $P::SOURCES[$pr->source] ?? '—' }}</div>
                <div class="col-sm-6 col-md-4"><small class="text-muted d-block">Assigned to</small>{{ $pr->assigned_to ?? '—' }}</div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Next follow-up</small>
                    <span class="{{ $pr->follow_up_overdue ? 'text-danger fw-semibold' : '' }}">{{ optional($pr->next_follow_up_date)->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Address</small>
                    {!! nl2br(e(collect([$pr->address, $pr->city, $pr->country])->filter()->implode("\n") ?: '—')) !!}
                </div>
                <div class="col-md-6"><small class="text-muted d-block">Created</small>{{ optional($pr->created_at)->format('Y-m-d') }}</div>
                <div class="col-12"><small class="text-muted d-block">Notes</small>{!! nl2br(e($pr->notes ?: '—')) !!}</div>
            </div>
        </div>

        @include('crm.contacts._list', ['owner' => $pr, 'readOnly' => $locked])

        <div class="mb-5"></div>
    </div>
@endsection

@push('styles')
    <style>
        .pipeline { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; }
        .pipeline-step .btn { font-size: .85rem; white-space: nowrap; }
        .pipeline-step .btn:disabled { opacity: 1; }
        .btn-success-subtle { background: #d1e7dd; color: #0f5132; border-color: #badbcc; }
        @media (max-width: 767.98px) {
            .pipeline { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
@endpush
