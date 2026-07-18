@extends('admin.layouts.admin')
@section('title', isset($sizeTier) ? 'Edit Size Tier' : 'Create Size Tier')
@section('page_title', isset($sizeTier) ? 'Edit Size Tier' : 'Create Size Tier')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.size-tiers.index') }}">Size Tiers</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($sizeTier) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($sizeTier) ? route('admin.calc.size-tiers.update', $sizeTier->id) : route('admin.calc.size-tiers.store') }}" method="POST">
        @csrf @if(isset($sizeTier))@method('PUT')@endif

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Key <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key', $sizeTier->key ?? '') }}" required>
                @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $sizeTier->name ?? '') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" name="description" maxlength="500">{{ old('description', $sizeTier->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.size-tiers.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($sizeTier) ? 'Update Size Tier' : 'Save Size Tier' }}</button>
        </div>
    </form>
</div></div></div></div>
@endsection
