@extends('layouts.app')

@section('title', 'Roles & permissions')

@section('content')
    @php $me = auth()->user(); @endphp

    <div class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="mb-0">Roles &amp; permissions</h1>
            @canroute('admin.roles.create')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary rounded-pill shadow-sm">
                    <i class="bi bi-shield-plus"></i> New role
                </a>
            @endcanroute
        </div>

        @include('admin._nav')

        <div class="row g-3">
            @forelse($roles as $role)
                @php
                    $count = $role->permissionCount();
                    $editable = $me->isSuperAdmin() || (! $role->is_super && $me->role_id !== $role->id);
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm h-100 {{ $role->is_super ? 'border-danger-subtle' : '' }}">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h2 class="h5 mb-0">
                                    <i class="bi {{ $role->is_super ? 'bi-shield-fill-check text-danger' : 'bi-shield-check text-primary' }}"></i>
                                    {{ $role->name }}
                                </h2>
                                <span class="badge bg-light text-dark border text-nowrap"><i class="bi bi-people"></i> {{ $role->users_count }}</span>
                            </div>
                            <p class="text-muted small flex-grow-1">{{ $role->description ?: 'No description.' }}</p>

                            <div class="small mb-1 d-flex justify-content-between">
                                <span>{{ $role->is_super ? 'Full access to everything' : "{$count} of {$total} permissions" }}</span>
                                <span>{{ round($count / max(1, $total) * 100) }}%</span>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar {{ $role->is_super ? 'bg-danger' : '' }}" style="width: {{ round($count / max(1, $total) * 100) }}%"></div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @if($editable)
                                    @canroute('admin.roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> {{ $role->is_super ? 'Rename' : 'Edit permissions' }}
                                        </a>
                                    @endcanroute
                                @endif
                                @if(! $role->is_super)
                                    @canroute('admin.roles.create')
                                        <a href="{{ route('admin.roles.create', ['copy' => $role->id]) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-copy"></i> Duplicate
                                        </a>
                                    @endcanroute
                                @endif
                                @if($editable && ! $role->is_super)
                                    @canroute('admin.roles.destroy')
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                            onsubmit="return confirm('Delete the role {{ addslashes($role->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" @disabled($role->users_count > 0)
                                                title="{{ $role->users_count ? 'Still assigned to users' : 'Delete' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcanroute
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">No roles yet.</div></div>
            @endforelse
        </div>
    </div>
@endsection
