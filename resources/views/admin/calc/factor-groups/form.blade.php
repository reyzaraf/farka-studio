@extends('admin.layouts.admin')
@section('title', isset($group) ? 'Edit Factor Group' : 'Create Factor Group')
@section('page_title', isset($group) ? 'Edit Factor Group' : 'Create Factor Group')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.factor-groups.index') }}">Weighting Factors</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($group) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
@php
    $initialOptions = old('options');
    if (!$initialOptions) {
        $initialOptions = isset($group)
            ? $group->options->map(fn ($o) => ['label' => $o->label, 'multiplier' => $o->multiplier, 'note' => $o->note, 'is_default' => $o->is_default])->all()
            : [['label' => '', 'multiplier' => '', 'note' => '', 'is_default' => true]];
    }
    $oldDefaultIndex = old('default_index');
    if ($oldDefaultIndex === null) {
        foreach ($initialOptions as $idx => $opt) {
            if (!empty($opt['is_default'])) { $oldDefaultIndex = $idx; break; }
        }
        $oldDefaultIndex = $oldDefaultIndex ?? 0;
    }
@endphp
<div class="row"><div class="col-lg-9 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($group) ? route('admin.calc.factor-groups.update', $group->id) : route('admin.calc.factor-groups.store') }}" method="POST">
        @csrf @if(isset($group))@method('PUT')@endif

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Key <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key', $group->key ?? '') }}" required>
                @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $group->name ?? '') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <h6 class="mt-4 d-flex justify-content-between align-items-center">
            <span>Options</span>
            <button type="button" id="add-option" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus"></i> Add option</button>
        </h6>
        @error('options')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        <div class="table-responsive">
            <table class="table table-sm align-middle" id="options-table">
                <thead><tr><th>Label</th><th style="width:140px">Multiplier</th><th>Note</th><th class="text-center" style="width:70px">Default</th><th style="width:50px"></th></tr></thead>
                <tbody id="options-body">
                @foreach($initialOptions as $i => $opt)
                    <tr class="option-row">
                        <td><input type="text" class="form-control form-control-sm" data-field="label" name="options[{{ $i }}][label]" value="{{ $opt['label'] ?? '' }}" required></td>
                        <td><input type="number" step="0.0001" min="0" class="form-control form-control-sm" data-field="multiplier" name="options[{{ $i }}][multiplier]" value="{{ $opt['multiplier'] ?? '' }}" required></td>
                        <td><input type="text" class="form-control form-control-sm" data-field="note" name="options[{{ $i }}][note]" value="{{ $opt['note'] ?? '' }}"></td>
                        <td class="text-center"><input type="radio" class="form-check-input" name="default_index" value="{{ $i }}" @checked((int) $oldDefaultIndex === $i)></td>
                        <td class="text-center"><button type="button" class="btn btn-icon btn-link-danger remove-option"><i class="ti ti-trash"></i></button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small">Select the "Default" radio on the option that should be pre-selected for users.</p>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.factor-groups.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($group) ? 'Update Factor Group' : 'Save Factor Group' }}</button>
        </div>
    </form>
</div></div></div></div>

<template id="option-row-template">
    <tr class="option-row">
        <td><input type="text" class="form-control form-control-sm" data-field="label" required></td>
        <td><input type="number" step="0.0001" min="0" class="form-control form-control-sm" data-field="multiplier" required></td>
        <td><input type="text" class="form-control form-control-sm" data-field="note"></td>
        <td class="text-center"><input type="radio" class="form-check-input" data-field="default_index"></td>
        <td class="text-center"><button type="button" class="btn btn-icon btn-link-danger remove-option"><i class="ti ti-trash"></i></button></td>
    </tr>
</template>

@push('scripts')
<script>
(function () {
    const body = document.getElementById('options-body');
    const template = document.getElementById('option-row-template');

    function reindex() {
        const rows = body.querySelectorAll('tr.option-row');
        rows.forEach(function (row, i) {
            row.querySelector('[data-field="label"]').name = 'options[' + i + '][label]';
            row.querySelector('[data-field="multiplier"]').name = 'options[' + i + '][multiplier]';
            row.querySelector('[data-field="note"]').name = 'options[' + i + '][note]';
            const radio = row.querySelector('[data-field="default_index"], input[type="radio"]');
            radio.name = 'default_index';
            radio.value = i;
        });
    }

    document.getElementById('add-option').addEventListener('click', function () {
        const row = template.content.firstElementChild.cloneNode(true);
        body.appendChild(row);
        reindex();
    });

    body.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-option');
        if (!btn) return;
        const rows = body.querySelectorAll('tr.option-row');
        if (rows.length <= 1) return; // keep at least one option row
        btn.closest('tr').remove();
        reindex();
    });
})();
</script>
@endpush
@endsection
