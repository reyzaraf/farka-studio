@extends('admin.layouts.admin')
@section('title', 'Zonasi')
@section('page_title', 'Zonasi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Zonasi</li>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">@endpush
@section('content')
<div class="row"><div class="col-xl-12"><div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Zonasi</h5>
        @can('create_calculator')<a href="{{ route('admin.calc.zonasi.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add Zonasi</a>@endcan
    </div>
    <div class="card-body">
        <div class="mb-3"><input type="search" id="table-search" class="form-control form-control-sm" placeholder="Search zonasi…"></div>
        <div class="table-responsive">
            <table id="zonasi-table" class="table table-striped table-bordered nowrap">
                <thead><tr><th>Code</th><th>Name</th><th>KDB%</th><th>KLB</th><th>RTH%</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($zonasis as $zonasi)
                    <tr>
                        <td><code>{{ $zonasi->code }}</code></td>
                        <td>{{ $zonasi->name }}</td>
                        <td>{{ rtrim(rtrim(number_format($zonasi->kdb * 100, 2), '0'), '.') }}%</td>
                        <td>{{ rtrim(rtrim(number_format($zonasi->klb, 2), '0'), '.') }}</td>
                        <td>{{ rtrim(rtrim(number_format($zonasi->rth * 100, 2), '0'), '.') }}%</td>
                        <td class="text-end">
                            @can('edit_calculator')<a href="{{ route('admin.calc.zonasi.edit', $zonasi->id) }}" class="btn btn-icon btn-link-success"><i class="ti ti-edit"></i></a>@endcan
                            @can('delete_calculator')
                            <form action="{{ route('admin.calc.zonasi.destroy', $zonasi->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-icon btn-link-danger delete-btn" data-name="{{ $zonasi->code }}"><i class="ti ti-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No zonasi found.</td></tr>
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
$(function(){ var t=$('#zonasi-table').DataTable({paging:true,pageLength:25,dom:'rtip'}); $('#table-search').on('input',function(){t.search(this.value).draw();}); });
</script>
@endpush
