@extends('admin.layouts.admin')

@section('title', 'Projects')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Projects</li>
@endsection
@section('page_title', 'Projects Management')

@push('styles')
    <!-- data tables css -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="row">
    <!-- [ basic-table ] start -->
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>All Projects</h5>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add New Project
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="table-responsive dt-responsive">
                    <table id="projects-table" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Architect</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                            <tr>
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
                                <td class="text-end">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-icon btn-link-success" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No projects found.</td>
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
<!-- datatable Js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#projects-table').DataTable({
            "pageLength": 10,
            "order": [[ 0, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [1, 7] } // Disable sorting on thumbnail and actions
            ]
        });
    });

    // SweetAlert Delete Confirmation
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });
    });
</script>
@endpush
