@extends('layouts.app')

@section('title', 'Prospects')

@section('content')
    @php
        $P = \App\Models\CRM\Prospect::class;
        $money = fn($v) => number_format((float) $v, 2);
    @endphp

    <div class="container mt-4">
        <h1 class="mb-4">List of Prospects</h1>

        <div class="mb-3">
            <a class="btn btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                href="{{ route('crm.prospects.create') }}">
                <i class="bi bi-plus-lg"></i> Add a New Prospect
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Pipeline summary --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Open prospects', $summary['open'], 'bi-bullseye', 'primary'],
                ['Pipeline value', $money($summary['open_value']), 'bi-cash-stack', 'info'],
                ['Weighted forecast', $money($summary['weighted']), 'bi-graph-up-arrow', 'success'],
                ['Win rate', $summary['win_rate'] === null ? '—' : $summary['win_rate'] . ' %', 'bi-trophy', 'warning'],
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

        {{-- Stage pills --}}
        <div class="d-flex flex-nowrap flex-md-wrap gap-2 mb-3 overflow-auto pb-1">
            <a href="{{ route('crm.prospects.index') }}"
                class="btn btn-sm rounded-pill text-nowrap {{ !request('status') && !request('follow_up') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
            <a href="{{ route('crm.prospects.index', ['status' => 'open']) }}"
                class="btn btn-sm rounded-pill text-nowrap {{ request('status') === 'open' ? 'btn-dark' : 'btn-outline-secondary' }}">Open</a>
            @foreach($P::STATUSES as $key => $label)
                <a href="{{ route('crm.prospects.index', ['status' => $key]) }}"
                    class="btn btn-sm rounded-pill text-nowrap {{ request('status') === $key ? 'btn-dark' : 'btn-outline-secondary' }}">
                    {{ $label }} <span class="badge bg-{{ $P::STATUS_COLORS[$key] }} ms-1">{{ $pipeline[$key]->total ?? 0 }}</span>
                </a>
            @endforeach
            <a href="{{ route('crm.prospects.index', ['follow_up' => 1]) }}"
                class="btn btn-sm rounded-pill text-nowrap {{ request('follow_up') ? 'btn-danger' : 'btn-outline-danger' }}">
                <i class="bi bi-alarm"></i> Follow-ups due <span class="badge bg-danger ms-1">{{ $summary['follow_ups'] }}</span>
            </a>
        </div>

        {{-- Filter & search form --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('crm.prospects.index') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <div class="col-md-5">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Name, code, contact, email, city or industry..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="source" class="form-label mb-1">Source</label>
                        <select name="source" id="source" class="form-select">
                            <option value="">All sources</option>
                            @foreach($P::SOURCES as $key => $label)
                                <option value="{{ $key }}" @selected(request('source') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="assigned_to" class="form-label mb-1">Assigned to</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">Everyone</option>
                            @foreach($assignees as $name)
                                <option value="{{ $name }}" @selected(request('assigned_to') === $name)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('crm.prospects.index') }}" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if($prospects->isEmpty())
            <div class="alert alert-info">No prospects found.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="prospectsTable" class="table table-striped table-hover table-bordered align-middle mb-0 text-nowrap">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Prospect</th>
                                    <th>Primary contact</th>
                                    <th>Status</th>
                                    <th class="text-end">Value</th>
                                    <th class="text-end">Prob.</th>
                                    <th>Next follow-up</th>
                                    <th>Assigned to</th>
                                    <th class="text-center" style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prospects as $prospect)
                                    <tr>
                                        <td>{{ $prospect->code }}</td>
                                        <td>
                                            <a href="{{ route('crm.prospects.show', $prospect) }}" class="fw-semibold text-decoration-none">{{ $prospect->name }}</a>
                                            <small class="d-block text-muted">
                                                {{ collect([$prospect->industry, $prospect->city, $P::SOURCES[$prospect->source] ?? null])->filter()->implode(' · ') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($prospect->primaryContact)
                                                <a href="{{ route('crm.contacts.show', $prospect->primaryContact) }}">{{ $prospect->primaryContact->full_name }}</a>
                                                <small class="d-block text-muted">{{ $prospect->primaryContact->mobile ?? $prospect->primaryContact->email }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $P::STATUS_COLORS[$prospect->status] }}">{{ $P::STATUSES[$prospect->status] }}</span>
                                            @if($prospect->isConverted())
                                                <i class="bi bi-person-check-fill text-success" title="Converted to customer"></i>
                                            @endif
                                        </td>
                                        <td class="text-end" data-order="{{ (float) $prospect->estimated_value }}">
                                            {{ $money($prospect->estimated_value) }} {{ $prospect->currency }}
                                        </td>
                                        <td class="text-end">{{ $prospect->probability }} %</td>
                                        <td class="{{ $prospect->follow_up_overdue ? 'text-danger fw-semibold' : '' }}"
                                            data-order="{{ optional($prospect->next_follow_up_date)->format('Y-m-d') ?? '9999' }}">
                                            {{ optional($prospect->next_follow_up_date)->format('Y-m-d') ?? '—' }}
                                            @if($prospect->follow_up_overdue)
                                                <i class="bi bi-alarm-fill" title="Follow-up overdue"></i>
                                            @endif
                                        </td>
                                        <td>{{ $prospect->assigned_to ?? '—' }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('crm.prospects.show', $prospect) }}" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                                                <a href="{{ route('crm.prospects.edit', $prospect) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                <form action="{{ route('crm.prospects.destroy', $prospect) }}" method="POST"
                                                    onsubmit="return confirm('Delete this prospect and its contacts?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
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
                        Showing {{ $prospects->firstItem() }} to {{ $prospects->lastItem() }} of {{ $prospects->total() }} prospects
                    </small>
                    {{ $prospects->links() }}
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
            $('#prospectsTable').DataTable({
                paging: false, searching: false, info: false, ordering: true, order: [], autoWidth: false,
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        });
    </script>
@endpush
