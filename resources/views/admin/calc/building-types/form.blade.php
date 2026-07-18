@extends('admin.layouts.admin')
@section('title', isset($buildingType) ? 'Edit Building Type' : 'Create Building Type')
@section('page_title', isset($buildingType) ? 'Edit Building Type' : 'Create Building Type')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.building-types.index') }}">Building Types</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($buildingType) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($buildingType) ? route('admin.calc.building-types.update', $buildingType->id) : route('admin.calc.building-types.store') }}" method="POST">
        @csrf @if(isset($buildingType))@method('PUT')@endif

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Key <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key', $buildingType->key ?? '') }}" required>
                @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $buildingType->name ?? '') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Price per m² (Rp) <span class="text-danger">*</span></label>
            <input type="number" step="1" min="0" class="form-control @error('price_per_m2') is-invalid @enderror" name="price_per_m2" value="{{ old('price_per_m2', $buildingType->price_per_m2 ?? '') }}" required>
            @error('price_per_m2')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.building-types.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($buildingType) ? 'Update Building Type' : 'Save Building Type' }}</button>
        </div>
    </form>
</div></div></div></div>
@endsection
