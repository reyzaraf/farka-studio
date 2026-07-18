@extends('admin.layouts.admin')
@section('title', isset($zonasi) ? 'Edit Zonasi' : 'Create Zonasi')
@section('page_title', isset($zonasi) ? 'Edit Zonasi' : 'Create Zonasi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.zonasi.index') }}">Zonasi</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($zonasi) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($zonasi) ? route('admin.calc.zonasi.update', $zonasi->id) : route('admin.calc.zonasi.store') }}" method="POST">
        @csrf @if(isset($zonasi))@method('PUT')@endif

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $zonasi->code ?? '') }}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $zonasi->name ?? '') }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">KDB (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" max="100" class="form-control @error('kdb') is-invalid @enderror" name="kdb" value="{{ old('kdb', isset($zonasi) ? $zonasi->kdb * 100 : '') }}" required>
                @error('kdb')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">KLB (ratio) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" class="form-control @error('klb') is-invalid @enderror" name="klb" value="{{ old('klb', $zonasi->klb ?? '') }}" required>
                @error('klb')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">KTB (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" max="100" class="form-control @error('ktb') is-invalid @enderror" name="ktb" value="{{ old('ktb', isset($zonasi) ? $zonasi->ktb * 100 : '') }}" required>
                @error('ktb')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">RTH (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" max="100" class="form-control @error('rth') is-invalid @enderror" name="rth" value="{{ old('rth', isset($zonasi) ? $zonasi->rth * 100 : '') }}" required>
                @error('rth')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <p class="text-muted small">KDB, KTB and RTH are entered as percentages and stored as fractions. KLB is stored as-is (a ratio).</p>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.zonasi.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($zonasi) ? 'Update Zonasi' : 'Save Zonasi' }}</button>
        </div>
    </form>
</div></div></div></div>
@endsection
