@extends('admin.layouts.admin')

@section('title', isset($person) ? 'Edit Team Member' : 'Add Team Member')
@section('page-title', isset($person) ? 'Edit Team Member' : 'Add Team Member')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.key-people.index') }}">Key People</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($person) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ isset($person) ? 'Edit Team Member Info' : 'New Team Member Details' }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($person) ? route('admin.key-people.update', $person->id) : route('admin.key-people.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($person))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                               value="{{ old('name', $person->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="role">Role / Position <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('role') is-invalid @enderror" id="role" name="role" 
                               value="{{ old('role', $person->role ?? '') }}" required>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="image_url">Profile Picture</label>
                        @if(isset($person) && $person->image_url)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $person->image_url) }}" alt="{{ $person->name }}" class="img-thumbnail" width="150">
                            </div>
                        @endif
                        <input type="file" class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url" accept="image/*">
                        <small class="form-text text-muted">Upload a new image to replace the existing one.</small>
                        @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Biography / Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $person->description ?? '') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="order">Display Order Sort</label>
                        <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order" 
                               value="{{ old('order', $person->order ?? ($nextOrder ?? 0)) }}">
                        <small class="form-text text-muted">Lower numbers appear first. Automatically set to appear at the end.</small>
                        @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.key-people.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ isset($person) ? 'Update Member' : 'Save Member' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
