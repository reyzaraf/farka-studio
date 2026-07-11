@extends('admin.layouts.admin')

@section('title', 'Categories')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Categories</li>
@endsection
@section('page_title', 'Category Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Categories</h5>
                @can('create_categories')
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add New Category
                </a>
                @endcan
            </div>
            <div class="card-body">

                @can('delete_categories')
                <div class="mb-3 text-end">
                    <button type="button" id="bulk-delete-btn" class="btn btn-sm btn-danger" disabled>
                        <i class="ti ti-trash"></i> Delete Selected (<span id="bulk-count">0</span>)
                    </button>
                </div>
                @endcan

                <form id="bulk-delete-form" action="{{ route('admin.categories.bulk-destroy') }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                    <div id="bulk-ids"></div>
                </form>

                <div class="table-responsive dt-responsive">
                    <table id="categories-table" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="check-all" class="form-check-input"></th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Projects Count</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td><input type="checkbox" class="form-check-input row-check" value="{{ $category->id }}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>
                                    <span class="badge bg-light-primary rounded-pill f-12">{{ $category->projects_count }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex">
                                        @can('edit_categories')
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-icon btn-link-success" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete_categories')
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete" data-name="{{ $category->name }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No categories found.</td>
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
        var table = $('#categories-table').DataTable({
            "pageLength": 10,
            "order": [[ 1, "asc" ]],
            "columnDefs": [{ "orderable": false, "targets": [0, 5] }]
        });

        function updateBulk() {
            var count = table.$('.row-check:checked').length;
            $('#bulk-count').text(count);
            $('#bulk-delete-btn').prop('disabled', count === 0);
        }
        $('#categories-table tbody').on('change', '.row-check', updateBulk);
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
            var msg = 'Delete ' + ids.length + ' selected category(ies)? This action cannot be undone.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Delete selected?', text: msg, icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete them!' })
                    .then(function (r) { if (r.isConfirmed) doDelete(); });
            } else if (window.confirm(msg)) { doDelete(); }
        });
    });
</script>
@endpush
