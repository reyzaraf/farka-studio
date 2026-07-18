@extends('admin.layouts.admin')
@section('title', 'Size Tiers')
@section('page_title', 'Size Tiers')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Size Tiers</li>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">@endpush
@section('content')
<div class="row"><div class="col-xl-12"><div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Size Tiers</h5>
        @can('create_calculator')<a href="{{ route('admin.calc.size-tiers.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add Size Tier</a>@endcan
    </div>
    <div class="card-body">
        <div class="mb-3"><input type="search" id="table-search" class="form-control form-control-sm" placeholder="Search size tiers…"></div>
        <div class="table-responsive">
            <table id="size-tiers-table" class="table table-striped table-bordered nowrap">
                <thead><tr><th>Name</th><th>Key</th><th>Description</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($sizeTiers as $sizeTier)
                    <tr>
                        <td>{{ $sizeTier->name }}</td>
                        <td><code>{{ $sizeTier->key }}</code></td>
                        <td>{{ $sizeTier->description }}</td>
                        <td class="text-end">
                            @can('edit_calculator')<a href="{{ route('admin.calc.size-tiers.edit', $sizeTier->id) }}" class="btn btn-icon btn-link-success"><i class="ti ti-edit"></i></a>@endcan
                            @can('delete_calculator')
                            <form action="{{ route('admin.calc.size-tiers.destroy', $sizeTier->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-icon btn-link-danger delete-btn" data-name="{{ $sizeTier->name }}"><i class="ti ti-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No size tiers found.</td></tr>
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
$(function(){ var t=$('#size-tiers-table').DataTable({paging:true,pageLength:25,dom:'rtip'}); $('#table-search').on('input',function(){t.search(this.value).draw();}); });
</script>
@endpush
