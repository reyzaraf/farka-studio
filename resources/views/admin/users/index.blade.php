@extends('admin.layouts.admin')

@section('title', 'System Users')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Users</li>
@endsection
@section('page_title', 'User Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Users</h5>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add New User
                </a>
            </div>
            <div class="card-body">

                <div class="mb-3 text-end">
                    <button type="button" id="bulk-delete-btn" class="btn btn-sm btn-danger" disabled>
                        <i class="ti ti-trash"></i> Delete Selected (<span id="bulk-count">0</span>)
                    </button>
                </div>

                <form id="bulk-delete-form" action="{{ route('admin.users.bulk-destroy') }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                    <div id="bulk-ids"></div>
                </form>

                <div class="table-responsive dt-responsive">
                    <table id="users-table" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="check-all" class="form-check-input"></th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    @if(auth()->id() !== $user->id)
                                        <input type="checkbox" class="form-check-input row-check" value="{{ $user->id }}">
                                    @endif
                                </td>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-inline-flex align-items-center">
                                        <div class="avtar avtar-s bg-light-primary text-primary me-2">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <span>{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->hasRole('super_admin'))
                                        <span class="badge bg-light-danger border border-danger">Super Admin</span>
                                    @else
                                        <span class="badge bg-light-secondary border border-secondary">{{ ucwords(str_replace('_', ' ', $user->getRoleNames()->first() ?? 'Administrator')) }}</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-icon btn-link-success" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete" data-name="{{ $user->name }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No users found.</td>
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
        var table = $('#users-table').DataTable({
            "pageLength": 10,
            "order": [[ 1, "asc" ]],
            "columnDefs": [{ "orderable": false, "targets": [0, 6] }]
        });

        function updateBulk() {
            var count = table.$('.row-check:checked').length;
            $('#bulk-count').text(count);
            $('#bulk-delete-btn').prop('disabled', count === 0);
        }
        $('#users-table tbody').on('change', '.row-check', updateBulk);
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
            var msg = 'Delete ' + ids.length + ' selected user(s)? This revokes their access entirely.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Delete selected?', text: msg, icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete them!' })
                    .then(function (r) { if (r.isConfirmed) doDelete(); });
            } else if (window.confirm(msg)) { doDelete(); }
        });
    });
</script>
@endpush
