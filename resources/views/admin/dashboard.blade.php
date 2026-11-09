@extends('layouts.app')

@section('title', 'Admin panel')

@section('content')
    <div class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="mb-0">Admin panel</h1>
            <div class="d-flex gap-2">
                @canroute('admin.users.create')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-pill shadow-sm">
                        <i class="bi bi-person-plus"></i> New user
                    </a>
                @endcanroute
                @canroute('admin.roles.create')
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-shield-plus"></i> New role
                    </a>
                @endcanroute
            </div>
        </div>

        @include('admin._nav')

        <div class="row g-3 mb-4">
            @foreach([
                ['Users', $stats['users'], 'bi-people', 'primary'],
                ['Active', $stats['active'], 'bi-person-check', 'success'],
                ['Deactivated', $stats['inactive'], 'bi-person-slash', 'secondary'],
                ['Roles', $stats['roles'], 'bi-shield-check', 'info'],
                ['Signed in today', $stats['logged_today'], 'bi-box-arrow-in-right', 'warning'],
                ['Never signed in', $stats['never_logged'], 'bi-hourglass', 'danger'],
            ] as [$title, $value, $icon, $tone])
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1"><i class="bi {{ $icon }} text-{{ $tone }}"></i> {{ $title }}</small>
                            <div class="fs-4 fw-bold">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-shield-check"></i> Users per role</span>
                        @canroute('admin.roles.index')
                            <a href="{{ route('admin.roles.index') }}" class="small">Manage roles</a>
                        @else
                        Manage roles
                        @endcanroute
                    </div>
                    <ul class="list-group list-group-flush">
                        @php $max = max(1, $roles->max('users_count') ?? 0, $unassigned); @endphp
                        @forelse($roles as $role)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>
                                        {{ $role->name }}
                                        @if($role->is_super)<span class="badge bg-danger-subtle text-danger-emphasis ms-1">Full access</span>@endif
                                    </span>
                                    <span class="fw-semibold">{{ $role->users_count }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar {{ $role->is_super ? 'bg-danger' : '' }}" style="width: {{ round($role->users_count / $max * 100) }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No roles yet.</li>
                        @endforelse
                        @if($unassigned)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-warning-emphasis"><i class="bi bi-exclamation-triangle"></i> No role (no access)</span>
                                    <span class="fw-semibold">{{ $unassigned }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: {{ round($unassigned / $max * 100) }}%"></div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-clock-history"></i> Recent sign-ins</span>
                        @canroute('admin.users.index')
                            <a href="{{ route('admin.users.index') }}" class="small">All users</a>
                        @else
                        All users
                        @endcanroute
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse($recentLogins as $u)
                            <li class="list-group-item d-flex align-items-center gap-3">
                                <span class="contact-avatar contact-avatar-sm">{{ $u->initials() }}</span>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-semibold text-truncate">{{ $u->name }}</div>
                                    <small class="text-muted text-truncate d-block">{{ $u->role?->name ?? 'No role' }} · {{ $u->last_login_ip }}</small>
                                </div>
                                <small class="text-muted text-nowrap" title="{{ $u->last_login_at }}">{{ $u->last_login_at->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nobody has signed in yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-person-plus"></i> Newest accounts</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="d-none d-md-table-cell">Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="d-none d-md-table-cell">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($newestUsers as $u)
                                    <tr>
                                        <td>
                                            @canroute('admin.users.edit')
                                                <a href="{{ route('admin.users.edit', $u) }}">{{ $u->name }}</a>
                                            @else
                                                {{ $u->name }}
                                            @endcanroute
                                        </td>
                                        <td class="d-none d-md-table-cell">{{ $u->email }}</td>
                                        <td>{{ $u->role?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge {{ $u->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $u->is_active ? 'Active' : 'Deactivated' }}</span>
                                        </td>
                                        <td class="d-none d-md-table-cell">{{ $u->created_at?->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
