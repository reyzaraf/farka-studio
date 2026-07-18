@extends('admin.layouts.admin')
@section('title', 'Rooms')
@section('page_title', 'Rooms & Areas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Rooms &amp; Areas</li>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">@endpush
@section('content')
<div class="row"><div class="col-xl-12"><div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Rooms</h5>
        @can('create_calculator')<a href="{{ route('admin.calc.rooms.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add Room</a>@endcan
    </div>
    <div class="card-body">
        <div class="mb-3"><input type="search" id="table-search" class="form-control form-control-sm" placeholder="Search rooms…"></div>
        <div class="table-responsive">
            <table id="rooms-table" class="table table-striped table-bordered nowrap">
                <thead><tr><th>Category</th><th>Name</th><th>Tiers</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td><span class="badge bg-light-secondary text-capitalize">{{ $room->category }}</span></td>
                        <td>{{ $room->name }}</td>
                        <td><span class="badge bg-light-primary rounded-pill">{{ $room->areas_count }}</span></td>
                        <td class="text-end">
                            @can('edit_calculator')<a href="{{ route('admin.calc.rooms.edit', $room->id) }}" class="btn btn-icon btn-link-success"><i class="ti ti-edit"></i></a>@endcan
                            @can('delete_calculator')
                            <form action="{{ route('admin.calc.rooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-icon btn-link-danger delete-btn" data-name="{{ $room->name }}"><i class="ti ti-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No rooms found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('admin_assets/js/plugins/dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function(){ var t=$('#rooms-table').DataTable({paging:true,pageLength:25,dom:'rtip'}); $('#table-search').on('input',function(){t.search(this.value).draw();}); });
</script>
@endpush
