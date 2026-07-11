@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
{{-- Quick actions --}}
<div class="row mb-1">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <span class="fw-medium me-2"><i class="ti ti-bolt text-warning"></i> Quick actions:</span>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-sm btn-primary"><i class="ti ti-plus"></i> New Project</a>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus"></i> New Category</a>
                <a href="{{ route('admin.key-people.create') }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus"></i> New Team Member</a>
                <a href="{{ route('admin.contact-settings.edit') }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-settings"></i> Page Settings</a>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-sm btn-light-info ms-auto"><i class="ti ti-external-link"></i> View Live Site</a>
            </div>
        </div>
    </div>
</div>

{{-- Stat cards (each links to its section) --}}
<div class="row">
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('admin.projects.index') }}" class="text-decoration-none text-reset">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <img src="{{ asset('admin_assets/images/widget/img-status-4.svg') }}" alt="img" class="img-fluid img-bg">
                    <h5 class="mb-4">Total Projects</h5>
                    <div class="d-flex align-items-center mt-3">
                        <h3 class="f-w-300 d-flex align-items-center m-b-0">{{ $stats['projects'] }}</h3>
                    </div>
                    <p class="text-muted mb-0 text-sm mt-3">Manage portfolio <i class="ti ti-arrow-right"></i></p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-sm-6">
        <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-reset">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <img src="{{ asset('admin_assets/images/widget/img-status-5.svg') }}" alt="img" class="img-fluid img-bg">
                    <h5 class="mb-4">Project Categories</h5>
                    <div class="d-flex align-items-center mt-3">
                        <h3 class="f-w-300 d-flex align-items-center m-b-0">{{ $stats['categories'] }}</h3>
                    </div>
                    <p class="text-muted mb-0 text-sm mt-3">Manage categories <i class="ti ti-arrow-right"></i></p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-sm-6">
        <a href="{{ route('admin.key-people.index') }}" class="text-decoration-none text-reset">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <img src="{{ asset('admin_assets/images/widget/img-status-6.svg') }}" alt="img" class="img-fluid img-bg">
                    <h5 class="mb-4">Team Members</h5>
                    <div class="d-flex align-items-center mt-3">
                        <h3 class="f-w-300 d-flex align-items-center m-b-0">{{ $stats['key_people'] }}</h3>
                    </div>
                    <p class="text-muted mb-0 text-sm mt-3">Manage team <i class="ti ti-arrow-right"></i></p>
                </div>
            </div>
        </a>
    </div>

    @role('super_admin')
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-reset">
            <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
                <div class="card-body">
                    <img src="{{ asset('admin_assets/images/widget/img-status-7.svg') }}" alt="img" class="img-fluid img-bg">
                    <h5 class="mb-4 text-white">Administrators</h5>
                    <div class="d-flex align-items-center mt-3">
                        <h3 class="text-white f-w-300 d-flex align-items-center m-b-0">{{ $stats['users'] }}</h3>
                    </div>
                    <p class="text-white text-opacity-75 mb-0 text-sm mt-3">Manage users <i class="ti ti-arrow-right"></i></p>
                </div>
            </div>
        </a>
    </div>
    @endrole
</div>

{{-- Recent projects + getting started --}}
<div class="row mt-1">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Projects</h5>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-link">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <tbody>
                            @forelse($recentProjects as $project)
                                <tr>
                                    <td style="width:60px;">
                                        @if($project->contents && $project->contents->first() && $project->contents->first()->image_url)
                                            <img src="{{ asset('storage/' . $project->contents->first()->image_url) }}" alt="thumb" style="width:44px;height:44px;object-fit:cover;border-radius:8px;">
                                        @else
                                            <div class="avtar avtar-s bg-light-primary"><i class="ti ti-photo"></i></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ \Illuminate\Support\Str::limit($project->title, 40) }}</div>
                                        <small class="text-muted">{{ $project->category->name ?? 'Uncategorized' }} · {{ $project->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-sm btn-link-success"><i class="ti ti-edit"></i> Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center text-muted py-4">
                                        No projects yet. <a href="{{ route('admin.projects.create') }}">Create your first project</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-help-circle text-primary"></i> Getting Started</h5>
            </div>
            <div class="card-body">
                <ul class="ps-3 mb-0" style="line-height: 2;">
                    <li>Add a portfolio piece under <strong>Projects &rarr; New Project</strong>.</li>
                    <li>Group work with <strong>Categories</strong>.</li>
                    <li>Introduce the studio in <strong>Team Members</strong>.</li>
                    <li>Update contact info &amp; the homepage video in <strong>Page Settings</strong>.</li>
                </ul>
                <hr>
                <p class="text-muted small mb-0">Tip: use <strong>View Site</strong> (top-right) to check how changes look on the live website after saving.</p>
            </div>
        </div>
    </div>
</div>
@endsection
