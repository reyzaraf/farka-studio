@extends('admin.layouts.admin')

@section('title', 'Projects')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Projects</li>
@endsection
@section('page_title', 'Projects Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Projects</h5>
                @can('create_projects')
                <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add New Project
                </a>
                @endcan
            </div>
            <div class="card-body">

                {{-- Filters + bulk toolbar --}}
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-sm-3">
                        <label class="form-label small mb-1">Filter by Category</label>
                        <select id="filter-category" class="form-select form-select-sm"><option value="">All categories</option></select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small mb-1">Filter by Status</label>
                        <select id="filter-status" class="form-select form-select-sm"><option value="">All statuses</option></select>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        @can('delete_projects')
                        <button type="button" id="bulk-delete-btn" class="btn btn-sm btn-danger" disabled>
                            <i class="ti ti-trash"></i> Delete Selected (<span id="bulk-count">0</span>)
                        </button>
                        @endcan
                    </div>
                </div>

                <form id="bulk-delete-form" action="{{ route('admin.projects.bulk-destroy') }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                    <div id="bulk-ids"></div>
                </form>

                <div class="table-responsive dt-responsive">
                    <table id="projects-table" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="check-all" class="form-check-input"></th>
                                <th>#</th>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Architect</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Published</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                            <tr>
                                <td><input type="checkbox" class="form-check-input row-check" value="{{ $project->id }}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($project->contents && $project->contents->first() && $project->contents->first()->image_url)
                                        <img src="{{ asset('storage/' . $project->contents->first()->image_url) }}" alt="Thumbnail" class="img-radius align-top m-r-15" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    @else
                                        <div class="avtar avtar-s bg-light-primary">
                                            <i class="ti ti-photo"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ Str::limit($project->title, 40) }}</td>
                                <td>{{ $project->category->name ?? 'N/A' }}</td>
                                <td>{{ $project->architect ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-light-info border border-info">{{ $project->order }}</span>
                                </td>
                                <td>
                                    @if($project->status)
                                        <span class="badge bg-light-secondary border border-secondary">{{ $project->status }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($project->is_published)
                                        <span class="badge bg-light-success border border-success">Published</span>
                                    @else
                                        <span class="badge bg-light-warning border border-warning">Draft</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex">
                                        @can('edit_projects')
                                        <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-icon btn-link-success" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete_projects')
                                        <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete" data-name="{{ $project->title }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No projects found. <a href="{{ route('admin.projects.create') }}">Add your first project</a>.</td>
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
        var table = $('#projects-table').DataTable({
            "pageLength": 10,
            "order": [[ 1, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [0, 2, 9] }
            ]
        });

        // Build filter dropdowns from the data actually present (no backend change)
        function fillFilter(colIdx, $select) {
            var seen = {};
            table.column(colIdx).data().each(function (val) {
                var text = $('<div>').html(val).text().trim();
                if (text && text !== '-' && text !== 'N/A' && !seen[text]) {
                    seen[text] = true;
                    $select.append($('<option>').val(text).text(text));
                }
            });
        }
        fillFilter(4, $('#filter-category'));
        fillFilter(7, $('#filter-status'));

        $('#filter-category').on('change', function () {
            table.column(4).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
        });
        $('#filter-status').on('change', function () {
            table.column(7).search(this.value ? this.value : '', false, true).draw();
        });

        /* ---------- Bulk selection + delete (works across all pages) ---------- */
        function updateBulk() {
            var count = table.$('.row-check:checked').length;
            $('#bulk-count').text(count);
            $('#bulk-delete-btn').prop('disabled', count === 0);
        }
        $('#projects-table tbody').on('change', '.row-check', updateBulk);
        $('#check-all').on('change', function () {
            var checked = this.checked;
            table.$('.row-check').prop('checked', checked); // all rows, including off-page
            updateBulk();
        });

        $('#bulk-delete-btn').on('click', function () {
            var ids = table.$('.row-check:checked').map(function () { return this.value; }).get();
            if (!ids.length) return;
            var doDelete = function () {
                var $container = $('#bulk-ids').empty();
                ids.forEach(function (id) {
                    $container.append($('<input>', { type: 'hidden', name: 'ids[]', value: id }));
                });
                $('#bulk-delete-form').submit();
            };
            var msg = 'Delete ' + ids.length + ' selected project(s)? This action cannot be undone.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Delete selected?', text: msg, icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete them!' })
                    .then(function (r) { if (r.isConfirmed) doDelete(); });
            } else if (window.confirm(msg)) { doDelete(); }
        });
    });
</script>
@endpush
