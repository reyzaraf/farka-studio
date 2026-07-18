@extends('admin.layouts.admin')
@section('title', isset($allocation) ? 'Edit Allocation' : 'Create Allocation')
@section('page_title', isset($allocation) ? 'Edit Allocation' : 'Create Allocation')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.allocations.index') }}">Allocations</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($allocation) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($allocation) ? route('admin.calc.allocations.update', $allocation->id) : route('admin.calc.allocations.store') }}" method="POST">
        @csrf @if(isset($allocation))@method('PUT')@endif

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-control @error('category') is-invalid @enderror" name="category" required>
                    <option value="">-- Select Category --</option>
                    @foreach(['pelaksanaan' => 'Pelaksanaan', 'perancangan' => 'Perancangan', 'persiapan' => 'Persiapan'] as $value => $text)
                        <option value="{{ $value }}" {{ old('category', $allocation->category ?? '') == $value ? 'selected' : '' }}>{{ $text }}</option>
                    @endforeach
                </select>
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Label <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('label') is-invalid @enderror" name="label" value="{{ old('label', $allocation->label ?? '') }}" required>
                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Percentage (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" class="form-control @error('percentage') is-invalid @enderror" name="percentage" value="{{ old('percentage', isset($allocation) ? $allocation->percentage * 100 : '') }}" required>
                @error('percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Note</label>
                <input type="text" class="form-control @error('note') is-invalid @enderror" name="note" value="{{ old('note', $allocation->note ?? '') }}">
                @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <p class="text-muted small">Percentage is entered as a percentage and stored as a fraction.</p>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_base" name="is_base" value="1" {{ old('is_base', $allocation->is_base ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_base">Base (e.g. "Bangunan")</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" {{ old('is_default', $allocation->is_default ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">Default</label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.allocations.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($allocation) ? 'Update Allocation' : 'Save Allocation' }}</button>
        </div>
    </form>
</div></div></div></div>
@endsection
