@extends('layouts.app')

@section('title', 'Users')

@section('content')
    @php $me = auth()->user(); @endphp

    <div class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="mb-0">Users</h1>
            @canroute('admin.users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-pill shadow-sm">
                    <i class="bi bi-person-plus"></i> New user
                </a>
            @endcanroute
        </div>

        @include('admin._nav')

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label for="search" class="form-label mb-1">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Name, email or phone..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="role" class="form-label mb-1">Role</label>
                        <select name="role" id="role" class="form-select">
                            <option value="">All roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(request('role') == $role->id)>{{ $role->name }}</option>
                            @endforeach
                            <option value="none" @selected(request('role') === 'none')>No role</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Deactivated</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-dark flex-grow-1"><i class="bi bi-search"></i> Filter</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="d-none d-lg-table-cell">Last sign-in</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $self = $me->is($user);
                                $locked = $user->isSuperAdmin() && ! $me->isSuperAdmin();
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="contact-avatar contact-avatar-sm">{{ $user->initials() }}</span>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-truncate">
                                                {{ $user->name }}
                                                @if($self)<span class="badge bg-info-subtle text-info-emphasis">You</span>@endif
                                            </div>
                                            <small class="text-muted text-truncate d-block">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->role)
                                        <span class="badge {{ $user->role->is_super ? 'bg-danger' : 'bg-primary-subtle text-primary-emphasis' }}">{{ $user->role->name }}</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis">No role</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Deactivated' }}</span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($user->last_login_at)
                                        <span title="{{ $user->last_login_at }}">{{ $user->last_login_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @unless($locked)
                                        <div class="d-inline-flex gap-1">
                                            @canroute('admin.users.edit')
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcanroute
                                            @canroute('admin.users.reset_link')
                                                @if($user->is_active)
                                                    <form action="{{ route('admin.users.reset_link', $user) }}" method="POST">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-secondary" title="Email a password reset link">
                                                            <i class="bi bi-envelope-arrow-up"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcanroute
                                            @if(! $self)
                                                @canroute('admin.users.toggle')
                                                    <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                                        @csrf
                                                        <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="bi {{ $user->is_active ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                                                        </button>
                                                    </form>
                                                @endcanroute
                                                @canroute('admin.users.destroy')
                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                        onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                @endcanroute
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small"><i class="bi bi-lock"></i> Administrator</span>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No users match these filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
