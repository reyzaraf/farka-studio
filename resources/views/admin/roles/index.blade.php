@extends('admin.layouts.admin')

@section('title', 'Roles')
@section('page-title', 'Roles & Permissions Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Roles</li>
@endsection

@push('styles')
    <!-- data tables css -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Roles</h5>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add New Role
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="table-responsive dt-responsive">
                    <table id="roles-table" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Role Name</th>
                                <th>Permissions Count</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-light-primary text-primary">{{ $role->name }}</span></td>
                                <td>{{ $role->permissions->count() }} permissions</td>
                                <td class="text-end">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-icon btn-link-success" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @if($role->name !== 'super_admin')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <button type="button" class="btn btn-icon btn-link-secondary" disabled title="Cannot delete Super Admin">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No roles found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#roles-table').DataTable({
            "pageLength": 10,
            "order": [[ 0, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [3] } 
            ]
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Removing this role will revoke access for all users assigned to it!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });
    });
</script>
@endpush
