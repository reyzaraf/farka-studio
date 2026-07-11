@extends('admin.layouts.admin')

@section('title', 'Team Members')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Team Members</li>
@endsection
@section('page_title', 'Team Members Management')

@push('styles')
    <style>
        #people-table .drag-handle { cursor: grab; }
        #people-table tr.sortable-ghost { opacity: .5; }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Our Team</h5>
                @can('create_key_people')
                <a href="{{ route('admin.key-people.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add Team Member
                </a>
                @endcan
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted"><i class="ti ti-grip-vertical"></i> Drag a row by the handle to reorder — the order is saved automatically.</small>
                    @can('delete_key_people')
                    <button type="button" id="bulk-delete-btn" class="btn btn-sm btn-danger" disabled>
                        <i class="ti ti-trash"></i> Delete Selected (<span id="bulk-count">0</span>)
                    </button>
                    @endcan
                </div>

                <form id="bulk-delete-form" action="{{ route('admin.key-people.bulk-destroy') }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                    <div id="bulk-ids"></div>
                </form>

                <div class="table-responsive">
                    <table id="people-table" class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th style="width:36px;"><input type="checkbox" id="check-all" class="form-check-input"></th>
                                <th style="width:70px;">Order</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="people-body">
                            @forelse($people as $person)
                            <tr data-id="{{ $person->id }}">
                                <td class="drag-handle text-muted" title="Drag to reorder"><i class="ti ti-grip-vertical"></i></td>
                                <td><input type="checkbox" class="form-check-input row-check" value="{{ $person->id }}"></td>
                                <td><span class="badge bg-light-info border border-info order-badge">{{ $person->order }}</span></td>
                                <td>
                                    @if($person->image_url)
                                        <img src="{{ asset('storage/' . $person->image_url) }}" alt="Photo" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    @else
                                        <div class="avtar avtar-s bg-light-primary"><i class="ti ti-user"></i></div>
                                    @endif
                                </td>
                                <td>{{ $person->name }}</td>
                                <td>{{ $person->role }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex">
                                        @can('edit_key_people')
                                        <a href="{{ route('admin.key-people.edit', $person->id) }}" class="btn btn-icon btn-link-success" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete_key_people')
                                        <form action="{{ route('admin.key-people.destroy', $person->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete" data-name="{{ $person->name }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No team members found.</td>
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
<script src="{{ asset('admin_assets/js/plugins/sortable.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var body = document.getElementById('people-body');
        var reorderUrl = "{{ route('admin.key-people.reorder') }}";
        var csrf = "{{ csrf_token() }}";

        /* ---------- Drag reorder with auto-save ---------- */
        if (window.Sortable && body) {
            Sortable.create(body, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function () {
                    var rows = body.querySelectorAll('tr[data-id]');
                    var ids = [];
                    rows.forEach(function (row, i) {
                        ids.push(row.getAttribute('data-id'));
                        var badge = row.querySelector('.order-badge');
                        if (badge) badge.textContent = i + 1;
                    });
                    var fd = new FormData();
                    ids.forEach(function (id) { fd.append('ids[]', id); });
                    fetch(reorderUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    }).then(function (r) {
                        if (!r.ok) throw new Error('failed');
                    }).catch(function () {
                        alert('Could not save the new order. Reloading to restore the correct order.');
                        window.location.reload();
                    });
                }
            });
        }

        /* ---------- Bulk selection + delete ---------- */
        var bulkBtn = document.getElementById('bulk-delete-btn');
        function checks() { return body ? body.querySelectorAll('.row-check') : []; }
        function updateBulk() {
            var count = body ? body.querySelectorAll('.row-check:checked').length : 0;
            var countEl = document.getElementById('bulk-count');
            if (countEl) countEl.textContent = count;
            if (bulkBtn) bulkBtn.disabled = count === 0;
        }
        if (body) body.addEventListener('change', function (e) { if (e.target.classList.contains('row-check')) updateBulk(); });
        var checkAll = document.getElementById('check-all');
        if (checkAll) checkAll.addEventListener('change', function () {
            checks().forEach(function (c) { c.checked = checkAll.checked; });
            updateBulk();
        });
        if (bulkBtn) bulkBtn.addEventListener('click', function () {
            var ids = Array.prototype.map.call(body.querySelectorAll('.row-check:checked'), function (c) { return c.value; });
            if (!ids.length) return;
            var doDelete = function () {
                var container = document.getElementById('bulk-ids');
                container.innerHTML = '';
                ids.forEach(function (id) {
                    var inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
                    container.appendChild(inp);
                });
                document.getElementById('bulk-delete-form').submit();
            };
            var msg = 'Delete ' + ids.length + ' selected team member(s)? This action cannot be undone.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Delete selected?', text: msg, icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete them!' })
                    .then(function (r) { if (r.isConfirmed) doDelete(); });
            } else if (window.confirm(msg)) { doDelete(); }
        });
    });
</script>
@endpush
