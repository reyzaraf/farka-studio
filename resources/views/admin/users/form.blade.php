@extends('admin.layouts.admin')

@section('title', isset($user) ? 'Edit User' : 'Create User')
@section('page-title', isset($user) ? 'Edit User' : 'Create User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($user) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ isset($user) ? 'Edit User Account' : 'New User Details' }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
                    @csrf
                    @if(isset($user))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                               value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                               value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password {{ isset($user) ? '(Leave blank to keep current)' : '*' }}</label>
                        <div class="input-group has-validation">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ !isset($user) ? 'required' : '' }} autocomplete="new-password">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password"><i class="ti ti-eye"></i></button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" {{ !isset($user) ? 'required' : '' }} autocomplete="new-password">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation"><i class="ti ti-eye"></i></button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Roles <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input @error('roles') is-invalid @enderror" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}"
                                        {{ (is_array(old('roles')) && in_array($role->name, old('roles'))) || (isset($userRoles) && in_array($role->name, $userRoles)) || (!isset($user) && !old('roles') && $role->name === 'editor') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update User' : 'Save User' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(this.getAttribute('data-target'));
            if (!input) return;
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.className = 'ti ti-eye-off';
            } else {
                input.type = 'password';
                if (icon) icon.className = 'ti ti-eye';
            }
        });
    });
</script>
@endpush
