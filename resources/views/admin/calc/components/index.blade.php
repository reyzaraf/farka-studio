@extends('admin.layouts.admin')
@section('title', 'Components')
@section('page_title', 'Quality Components')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Components</li>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">@endpush
@section('content')
<div class="row"><div class="col-xl-12"><div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Components</h5>
        @can('create_calculator')<a href="{{ route('admin.calc.components.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add Component</a>@endcan
    </div>
    <div class="card-body">
        <div class="mb-3"><input type="search" id="table-search" class="form-control form-control-sm" placeholder="Search components…"></div>
        <div class="table-responsive">
            <table id="components-table" class="table table-striped table-bordered nowrap">
                <thead><tr><th>Name</th><th>Standar</th><th>Optimal</th><th>Premium</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($components as $component)
                    <tr>
                        <td>{{ $component->name }}</td>
                        <td>{{ $component->standar }}</td>
                        <td>{{ $component->optimal }}</td>
                        <td>{{ $component->premium }}</td>
                        <td class="text-end">
                            @can('edit_calculator')<a href="{{ route('admin.calc.components.edit', $component->id) }}" class="btn btn-icon btn-link-success"><i class="ti ti-edit"></i></a>@endcan
                            @can('delete_calculator')
                            <form action="{{ route('admin.calc.components.destroy', $component->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-icon btn-link-danger delete-btn" data-name="{{ $component->name }}"><i class="ti ti-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No components found.</td></tr>
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
$(function(){ var t=$('#components-table').DataTable({paging:true,pageLength:25,dom:'rtip'}); $('#table-search').on('input',function(){t.search(this.value).draw();}); });
</script>
@endpush
