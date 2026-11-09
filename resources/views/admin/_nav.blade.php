{{-- Admin panel tabs --}}
<ul class="nav nav-pills flex-nowrap overflow-auto gap-1 mb-4 pb-1">
    @cananyroute('admin.dashboard')
        <li class="nav-item">
            <a class="nav-link text-nowrap {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-grid-1x2"></i> Overview
            </a>
        </li>
    @endcananyroute
    @canroute('admin.users.index')
        <li class="nav-item">
            <a class="nav-link text-nowrap {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
        </li>
    @endcanroute
    @canroute('admin.roles.index')
        <li class="nav-item">
            <a class="nav-link text-nowrap {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                <i class="bi bi-shield-check"></i> Roles &amp; permissions
            </a>
        </li>
    @endcanroute
</ul>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
