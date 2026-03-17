@extends('admin.layouts.admin')

@section('title', isset($role) ? 'Edit Role' : 'Create Role')
@section('page-title', isset($role) ? 'Edit Role' : 'Create Role')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($role) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ isset($role) ? 'Edit Role & Permissions' : 'New Role Details' }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($role) ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}" method="POST">
                    @csrf
                    @if(isset($role))
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label class="form-label" for="name">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                               value="{{ old('name', $role->name ?? '') }}" {{ (isset($role) && $role->name === 'super_admin') ? 'readonly' : 'required' }}>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if(isset($role) && $role->name === 'super_admin')
                            <small class="form-text text-muted">The super_admin role name cannot be changed.</small>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Assign Permissions</label>
                        
                        <div class="row">
                            @forelse($permissions as $group => $groupPerms)
                                <div class="col-md-12 mb-3">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center bg-light-primary rounded-top">
                                            <h6 class="mb-0 text-primary fw-bold text-capitalize"><i class="ti ti-shield"></i> {{ str_replace('_', ' ', $group) }}</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary select-all-btn" data-group="{{ $group }}" {{ (isset($role) && $role->name === 'super_admin') ? 'disabled' : '' }}>Select All</button>
                                        </div>
                                        <div class="card-body pt-3 pb-2">
                                            <div class="row">
                                            @foreach($groupPerms as $permission)
                                                <div class="col-md-3 mb-2">
                                                    <div class="form-check form-switch custom-switch-v1">
                                                        <input class="form-check-input perm-checkbox group-{{ $group }}" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" 
                                                            {{ (is_array(old('permissions')) && in_array($permission->name, old('permissions'))) || (isset($rolePermissions) && in_array($permission->name, $rolePermissions)) || (isset($role) && $role->name === 'super_admin') ? 'checked' : '' }}
                                                            {{ (isset($role) && $role->name === 'super_admin') ? 'disabled' : '' }}>
                                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                            {{ ucwords(str_replace('_', ' ', explode('_', $permission->name)[0])) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted">
                                    No permissions defined in the system. <br>
                                    <small>Permissions must be seeded manually in the database or via artisan.</small>
                                </div>
                            @endforelse
                        </div>
                        
                        @if(isset($role) && $role->name === 'super_admin')
                            <small class="form-text text-info mt-2 d-block"><i class="ti ti-info-circle"></i> The super_admin role inherently possesses all permissions regardless of these checkboxes.</small>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ isset($role) ? 'Update Role' : 'Save Role' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllBtns = document.querySelectorAll('.select-all-btn');
        
        selectAllBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const group = this.getAttribute('data-group');
                const checkboxes = document.querySelectorAll(`.perm-checkbox.group-${group}`);
                
                // Check if all are currently checked
                let allChecked = true;
                checkboxes.forEach(cb => {
                    if(!cb.checked) allChecked = false;
                });
                
                // Toggle state
                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                });
                
                // Update button text
                if (allChecked) {
                    this.textContent = 'Select All';
                    this.classList.replace('btn-primary', 'btn-outline-primary');
                } else {
                    this.textContent = 'Deselect All';
                    this.classList.replace('btn-outline-primary', 'btn-primary');
                }
            });
        });
        
        // Initial button text sync
        selectAllBtns.forEach(btn => {
            const group = btn.getAttribute('data-group');
            const checkboxes = document.querySelectorAll(`.perm-checkbox.group-${group}`);
            let allChecked = checkboxes.length > 0;
            checkboxes.forEach(cb => {
                if(!cb.checked) allChecked = false;
            });
            if (allChecked && checkboxes.length > 0) {
                btn.textContent = 'Deselect All';
                btn.classList.replace('btn-outline-primary', 'btn-primary');
            }
        });
    });
</script>
@endpush
