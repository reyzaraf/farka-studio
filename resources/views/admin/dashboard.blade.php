@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
<div class="row">
    <!-- Projects Stat -->
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <img src="{{ asset('admin_assets/images/widget/img-status-4.svg') }}" alt="img" class="img-fluid img-bg">
                <h5 class="mb-4">Total Projects</h5>
                <div class="d-flex align-items-center mt-3">
                    <h3 class="f-w-300 d-flex align-items-center m-b-0">{{ $stats['projects'] }}</h3>
                </div>
                <p class="text-muted mb-2 text-sm mt-3">Active projects in portfolio</p>
                <div class="progress" style="height: 7px">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categories Stat -->
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <img src="{{ asset('admin_assets/images/widget/img-status-5.svg') }}" alt="img" class="img-fluid img-bg">
                <h5 class="mb-4">Project Categories</h5>
                <div class="d-flex align-items-center mt-3">
                    <h3 class="f-w-300 d-flex align-items-center m-b-0">{{ $stats['categories'] }}</h3>
                </div>
                <p class="text-muted mb-2 text-sm mt-3">Different types of works</p>
                <div class="progress" style="height: 7px">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key People Stat -->
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <img src="{{ asset('admin_assets/images/widget/img-status-6.svg') }}" alt="img" class="img-fluid img-bg">
                <h5 class="mb-4">Team Members</h5>
                <div class="d-flex align-items-center mt-3">
                    <h3 class="f-w-300 d-flex align-items-center m-b-0">{{ $stats['key_people'] }}</h3>
                </div>
                <p class="text-muted mb-2 text-sm mt-3">Key people in about page</p>
                <div class="progress" style="height: 7px">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Stat -->
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
                <img src="{{ asset('admin_assets/images/widget/img-status-7.svg') }}" alt="img" class="img-fluid img-bg">
                <h5 class="mb-4 text-white">Administrators</h5>
                <div class="d-flex align-items-center mt-3">
                    <h3 class="text-white f-w-300 d-flex align-items-center m-b-0">{{ $stats['users'] }}</h3>
                </div>
                <p class="text-white text-opacity-75 mb-2 text-sm mt-3">Registered system users</p>
                <div class="progress bg-white bg-opacity-10" style="height: 7px">
                    <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-primary d-flex align-items-center" role="alert">
            <i class="ph-duotone ph-info flex-shrink-0 me-2 f-24"></i>
            <div>
                <strong>Welcome to the new Custom Admin Panel!</strong> 
                Use the sidebar navigation to manage your projects, categories, and site settings. All features inside this dashboard run entirely independent of the backend, completely free from Livewire conflicts.
            </div>
        </div>
    </div>
</div>
@endsection
