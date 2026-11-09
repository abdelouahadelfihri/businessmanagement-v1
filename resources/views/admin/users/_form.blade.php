@php
    $creating = ! $user->exists;
    $self = auth()->user()->is($user);
    $mode = old('password_mode', 'email');
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person"></i> Account</div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                        class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-7">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                        class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Used to sign in and to receive password reset links.</div>
                </div>
                <div class="col-md-5">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                        class="form-control @error('phone') is-invalid @enderror">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-key"></i> Password</div>
            <div class="card-body">
                @if($creating)
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="password_mode" id="mode_email" value="email" @checked($mode === 'email')>
                            <label class="form-check-label" for="mode_email">
                                Email the user a link to choose their own password
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="password_mode" id="mode_set" value="set" @checked($mode === 'set')>
                            <label class="form-check-label" for="mode_set">Set a password now</label>
                        </div>
                    </div>
                @else
                    <p class="text-muted small mb-3">Leave blank to keep the current password. To let the user pick a new one, use “Send reset link”.</p>
                @endif

                <div class="row g-3" id="passwordFields" @if($creating && $mode === 'email') hidden @endif>
                    <div class="col-md-6">
                        <label for="password" class="form-label">{{ $creating ? 'Password' : 'New password' }}</label>
                        <input type="password" name="password" id="password" autocomplete="new-password"
                            class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">At least 8 characters, letters and numbers.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-shield-check"></i> Access</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="role_id" class="form-label">Role</label>
                    <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" @disabled($self)>
                        <option value="">— No role (no access) —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                                {{ $role->name }}{{ $role->is_super ? ' — full access' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($self)
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        <div class="form-text">You cannot change your own role.</div>
                    @else
                        <div class="form-text">The role decides which screens and actions this user can use.</div>
                    @endif
                </div>

                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="{{ $self ? 1 : 0 }}">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1"
                        @checked(old('is_active', $user->is_active ?? true)) @disabled($self)>
                    <label class="form-check-label" for="is_active">Account active</label>
                    <div class="form-text">Deactivated users cannot sign in and are signed out right away.</div>
                </div>
            </div>
        </div>

        @unless($creating)
            <div class="card shadow-sm mb-3">
                <div class="card-body small text-muted">
                    <div><i class="bi bi-calendar-plus"></i> Created {{ $user->created_at?->format('d/m/Y H:i') }}</div>
                    <div><i class="bi bi-box-arrow-in-right"></i> Last sign-in:
                        {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') . ' from ' . $user->last_login_ip : 'never' }}
                    </div>
                </div>
            </div>
        @endunless
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('input[name="password_mode"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.getElementById('passwordFields').hidden = radio.value === 'email' && radio.checked;
            });
        });
    </script>
@endpush
