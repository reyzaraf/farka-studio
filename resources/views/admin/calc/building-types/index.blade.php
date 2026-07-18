@extends('admin.layouts.admin')
@section('title', 'Building Types')
@section('page_title', 'Building Types & Price')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Building Types</li>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">@endpush
@section('content')
<div class="row"><div class="col-xl-12"><div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Building Types</h5>
        @can('create_calculator')<a href="{{ route('admin.calc.building-types.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add Building Type</a>@endcan
    </div>
    <div class="card-body">
        <div class="mb-3"><input type="search" id="table-search" class="form-control form-control-sm" placeholder="Search building types…"></div>
        <div class="table-responsive">
            <table id="building-types-table" class="table table-striped table-bordered nowrap">
                <thead><tr><th>Name</th><th>Key</th><th>Price/m²</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($buildingTypes as $buildingType)
                    <tr>
                        <td>{{ $buildingType->name }}</td>
                        <td><code>{{ $buildingType->key }}</code></td>
                        <td>Rp {{ number_format($buildingType->price_per_m2, 0, ',', '.') }}</td>
                        <td class="text-end">
                            @can('edit_calculator')<a href="{{ route('admin.calc.building-types.edit', $buildingType->id) }}" class="btn btn-icon btn-link-success"><i class="ti ti-edit"></i></a>@endcan
                            @can('delete_calculator')
                            <form action="{{ route('admin.calc.building-types.destroy', $buildingType->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-icon btn-link-danger delete-btn" data-name="{{ $buildingType->name }}"><i class="ti ti-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No building types found.</td></tr>
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
$(function(){ var t=$('#building-types-table').DataTable({paging:true,pageLength:25,dom:'rtip'}); $('#table-search').on('input',function(){t.search(this.value).draw();}); });
</script>
@endpush
