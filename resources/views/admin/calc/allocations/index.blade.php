@extends('admin.layouts.admin')
@section('title', 'Allocations')
@section('page_title', 'Allocations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Allocations</li>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">@endpush
@section('content')
<div class="row"><div class="col-xl-12"><div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Allocations</h5>
        @can('create_calculator')<a href="{{ route('admin.calc.allocations.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add Allocation</a>@endcan
    </div>
    <div class="card-body">
        <div class="mb-3"><input type="search" id="table-search" class="form-control form-control-sm" placeholder="Search allocations…"></div>
        <div class="table-responsive">
            <table id="allocations-table" class="table table-striped table-bordered nowrap">
                <thead><tr><th>Category</th><th>Label</th><th>Percentage</th><th>Base?</th><th>Default?</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($allocations as $allocation)
                    <tr>
                        <td>{{ ucfirst($allocation->category) }}</td>
                        <td>{{ $allocation->label }}</td>
                        <td>{{ rtrim(rtrim(number_format($allocation->percentage * 100, 2), '0'), '.') }}%</td>
                        <td>{{ $allocation->is_base ? 'Yes' : 'No' }}</td>
                        <td>{{ $allocation->is_default ? 'Yes' : 'No' }}</td>
                        <td class="text-end">
                            @can('edit_calculator')<a href="{{ route('admin.calc.allocations.edit', $allocation->id) }}" class="btn btn-icon btn-link-success"><i class="ti ti-edit"></i></a>@endcan
                            @can('delete_calculator')
                            <form action="{{ route('admin.calc.allocations.destroy', $allocation->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-icon btn-link-danger delete-btn" data-name="{{ $allocation->label }}"><i class="ti ti-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No allocations found.</td></tr>
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
$(function(){ var t=$('#allocations-table').DataTable({paging:true,pageLength:25,dom:'rtip'}); $('#table-search').on('input',function(){t.search(this.value).draw();}); });
</script>
@endpush
