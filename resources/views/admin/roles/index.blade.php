@extends('admin.layouts.admin')

@section('title', 'Roles')
@section('page_title', 'Roles & Permissions Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Roles</li>
@endsection

@push('styles')
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

                <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
                    <input type="search" id="table-search" class="form-control form-control-sm table-search" placeholder="Search roles…" autocomplete="off">
                    <button type="button" id="bulk-delete-btn" class="btn btn-sm btn-danger" disabled>
                        <i class="ti ti-trash"></i> Delete Selected (<span id="bulk-count">0</span>)
                    </button>
                </div>

                <form id="bulk-delete-form" action="{{ route('admin.roles.bulk-destroy') }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                    <div id="bulk-ids"></div>
                </form>

                <div class="table-responsive dt-responsive">
                    <table id="roles-table" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="check-all" class="form-check-input"></th>
                                <th>#</th>
                                <th>Role Name</th>
                                <th>Permissions Count</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                            <tr>
                                <td>
                                    @if($role->name !== 'super_admin')
                                        <input type="checkbox" class="form-check-input row-check" value="{{ $role->id }}">
                                    @endif
                                </td>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-light-primary text-primary">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span></td>
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
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete" data-name="{{ $role->name }}">
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
                                <td colspan="5" class="text-center">No roles found.</td>
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
<script src="{{ asset('admin_assets/js/plugins/dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function () {
        var table = $('#roles-table').DataTable({
            "pageLength": 10,
            "dom": "rtip",          // drop the built-in search/length; our own search sits in the toolbar
            "order": [[ 1, "asc" ]],
            "columnDefs": [{ "orderable": false, "targets": [0, 4] }]
        });

        $('#table-search').on('input', function () { table.search(this.value).draw(); });

        function updateBulk() {
            var count = table.$('.row-check:checked').length;
            $('#bulk-count').text(count);
            $('#bulk-delete-btn').prop('disabled', count === 0);
        }
        $('#roles-table tbody').on('change', '.row-check', updateBulk);
        $('#check-all').on('change', function () {
            table.$('.row-check').prop('checked', this.checked);
            updateBulk();
        });
        $('#bulk-delete-btn').on('click', function () {
            var ids = table.$('.row-check:checked').map(function () { return this.value; }).get();
            if (!ids.length) return;
            var doDelete = function () {
                var $c = $('#bulk-ids').empty();
                ids.forEach(function (id) { $c.append($('<input>', { type: 'hidden', name: 'ids[]', value: id })); });
                $('#bulk-delete-form').submit();
            };
            var msg = 'Delete ' + ids.length + ' selected role(s)? Users assigned to them will lose that access.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Delete selected?', text: msg, icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete them!' })
                    .then(function (r) { if (r.isConfirmed) doDelete(); });
            } else if (window.confirm(msg)) { doDelete(); }
        });
    });
</script>
@endpush
