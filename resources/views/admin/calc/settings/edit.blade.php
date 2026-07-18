@extends('admin.layouts.admin')
@section('title', 'Calculator Settings')
@section('page_title', 'Calculator Settings')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item" aria-current="page">Calculator Settings</li>
@endsection
@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>Global Calculator Settings</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.calc.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Dana Darurat (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100"
                               class="form-control @error('dana_darurat_pct') is-invalid @enderror"
                               name="dana_darurat_pct"
                               value="{{ old('dana_darurat_pct', isset($settings['dana_darurat_pct']) ? $settings['dana_darurat_pct']->value * 100 : '') }}"
                               required>
                        @error('dana_darurat_pct')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sirkulasi (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100"
                               class="form-control @error('sirkulasi_pct') is-invalid @enderror"
                               name="sirkulasi_pct"
                               value="{{ old('sirkulasi_pct', isset($settings['sirkulasi_pct']) ? $settings['sirkulasi_pct']->value * 100 : '') }}"
                               required>
                        @error('sirkulasi_pct')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Toleransi Default (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0"
                               class="form-control @error('toleransi_default') is-invalid @enderror"
                               name="toleransi_default"
                               value="{{ old('toleransi_default', $settings['toleransi_default']->value ?? '') }}"
                               required>
                        @error('toleransi_default')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
