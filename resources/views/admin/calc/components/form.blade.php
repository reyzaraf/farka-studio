@extends('admin.layouts.admin')
@section('title', isset($component) ? 'Edit Component' : 'Create Component')
@section('page_title', isset($component) ? 'Edit Component' : 'Create Component')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.components.index') }}">Components</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($component) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($component) ? route('admin.calc.components.update', $component->id) : route('admin.calc.components.store') }}" method="POST">
        @csrf @if(isset($component))@method('PUT')@endif

        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $component->name ?? '') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Standar <span class="text-danger">*</span></label>
            <textarea class="form-control @error('standar') is-invalid @enderror" name="standar" maxlength="500" required>{{ old('standar', $component->standar ?? '') }}</textarea>
            @error('standar')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Optimal <span class="text-danger">*</span></label>
            <textarea class="form-control @error('optimal') is-invalid @enderror" name="optimal" maxlength="500" required>{{ old('optimal', $component->optimal ?? '') }}</textarea>
            @error('optimal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Premium <span class="text-danger">*</span></label>
            <textarea class="form-control @error('premium') is-invalid @enderror" name="premium" maxlength="500" required>{{ old('premium', $component->premium ?? '') }}</textarea>
            @error('premium')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.components.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($component) ? 'Update Component' : 'Save Component' }}</button>
        </div>
    </form>
</div></div></div></div>
@endsection
