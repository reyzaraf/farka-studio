@extends('admin.layouts.admin')

@section('title', 'Contact Settings')
@section('page-title', 'Company Contact Information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item" aria-current="page">Contact Settings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>Update Global Contact Details</h5>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <form action="{{ route('admin.contact-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label" for="email">Display Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                               value="{{ old('email', $setting->email ?? '') }}" placeholder="e.g. hello@farkastudio.test">
                        <small class="form-text text-muted">This email is shown publicly on the site.</small>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="phone">Contact Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" 
                               value="{{ old('phone', $setting->phone ?? '') }}" placeholder="e.g. +62 812 3456 7890">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5">
                        <label class="form-label" for="address">Studio Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" 
                                  rows="4" placeholder="Enter full physical address">{{ old('address', $setting->address ?? '') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
