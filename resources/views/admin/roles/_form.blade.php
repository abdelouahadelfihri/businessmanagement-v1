@php
    $checked = old('permissions', $granted);
    $isSuper = (bool) $role->is_super;
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body row g-3">
        <div class="col-md-5">
            <label for="name" class="form-label">Role name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}"
                class="form-control @error('name') is-invalid @enderror" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-7">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" id="description" value="{{ old('description', $role->description) }}"
                class="form-control" placeholder="What this role is for">
        </div>
    </div>
</div>

@if($isSuper)
    <div class="alert alert-warning">
        <i class="bi bi-shield-fill-check"></i>
        This is the administrator role: it always has every permission, including user and role management.
    </div>
@else
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h2 class="h5 mb-0">Permissions</h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-check-all="1"><i class="bi bi-check2-all"></i> Select all</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-check-all="0"><i class="bi bi-x-lg"></i> Clear</button>
        </div>
    </div>
    @if(count($grantable) < count(\App\Support\Permissions::all()))
        <p class="small text-muted">Greyed-out boxes are permissions you do not have yourself, so you cannot grant them.</p>
    @endif

    @foreach($groups as $group => $modules)
        @php $groupId = \Illuminate\Support\Str::slug($group); @endphp
        <div class="card shadow-sm mb-3 perm-group" id="grp-{{ $groupId }}">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $group }}</span>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="all-{{ $groupId }}" data-check-group="grp-{{ $groupId }}">
                    <label class="form-check-label small" for="all-{{ $groupId }}">Whole section</label>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start ps-3">Module</th>
                            @foreach($actions as $key => $label)
                                <th class="small fw-semibold text-nowrap">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module => $definition)
                            <tr class="perm-row">
                                <td class="text-start ps-3 text-nowrap">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="row-{{ $module }}" data-check-row>
                                        <label class="form-check-label" for="row-{{ $module }}">{{ $definition['label'] }}</label>
                                    </div>
                                </td>
                                @foreach($actions as $action => $label)
                                    <td>
                                        @if(in_array($action, $definition['actions'], true))
                                            @php $perm = "{$module}.{$action}"; @endphp
                                            <input class="form-check-input perm" type="checkbox" name="permissions[]" value="{{ $perm }}"
                                                data-action="{{ $action }}" aria-label="{{ $definition['label'] }}: {{ $label }}"
                                                @checked(in_array($perm, $checked, true)) @disabled(! in_array($perm, $grantable, true))>
                                        @else
                                            <span class="text-body-tertiary">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif

@push('scripts')
    <script>
        (function () {
            const boxes = (scope) => Array.from(scope.querySelectorAll('input.perm:not(:disabled)'));

            function refresh() {
                document.querySelectorAll('.perm-row').forEach(function (row) {
                    const b = boxes(row), toggle = row.querySelector('[data-check-row]');
                    const on = b.filter(x => x.checked).length;
                    toggle.checked = b.length > 0 && on === b.length;
                    toggle.indeterminate = on > 0 && on < b.length;
                    toggle.disabled = b.length === 0;
                });
                document.querySelectorAll('[data-check-group]').forEach(function (toggle) {
                    const b = boxes(document.getElementById(toggle.dataset.checkGroup));
                    const on = b.filter(x => x.checked).length;
                    toggle.checked = b.length > 0 && on === b.length;
                    toggle.indeterminate = on > 0 && on < b.length;
                });
            }

            document.querySelectorAll('[data-check-row]').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    boxes(toggle.closest('.perm-row')).forEach(x => x.checked = toggle.checked);
                    refresh();
                });
            });
            document.querySelectorAll('[data-check-group]').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    boxes(document.getElementById(toggle.dataset.checkGroup)).forEach(x => x.checked = toggle.checked);
                    refresh();
                });
            });
            document.querySelectorAll('[data-check-all]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    boxes(document).forEach(x => x.checked = btn.dataset.checkAll === '1');
                    refresh();
                });
            });

            // Any action other than "view" needs "view" on the same module; ticking it ticks view too.
            document.querySelectorAll('input.perm').forEach(function (box) {
                box.addEventListener('change', function () {
                    const row = box.closest('.perm-row');
                    const view = row.querySelector('input.perm[data-action="view"]');
                    if (box.checked && box.dataset.action !== 'view' && view && !view.disabled) view.checked = true;
                    if (!box.checked && box.dataset.action === 'view') {
                        row.querySelectorAll('input.perm:not(:disabled)').forEach(x => x.checked = false);
                    }
                    refresh();
                });
            });

            refresh();
        })();
    </script>
@endpush
