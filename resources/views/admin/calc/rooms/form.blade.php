@extends('admin.layouts.admin')
@section('title', isset($room) ? 'Edit Room' : 'Create Room')
@section('page_title', isset($room) ? 'Edit Room' : 'Create Room')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($room) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($room) ? route('admin.calc.rooms.update', $room->id) : route('admin.calc.rooms.store') }}" method="POST">
        @csrf @if(isset($room))@method('PUT')@endif
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category" class="form-select" required>
                    @foreach(['service','public','private','luxury'] as $c)
                        <option value="{{ $c }}" @selected(old('category', $room->category ?? '')===$c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input name="name" class="form-control" value="{{ old('name', $room->name ?? '') }}" required>
            </div>
        </div>

        <h6 class="mt-4">Area per Size Tier (Panjang × Lebar)</h6>
        <table class="table table-sm align-middle">
            <thead><tr><th>Tier</th><th>Panjang (m)</th><th>Lebar (m)</th></tr></thead>
            <tbody>
            @foreach($sizeTiers as $i => $tier)
                @php $existing = isset($areasByTier) ? ($areasByTier[$tier->id] ?? null) : null; @endphp
                <tr>
                    <td>{{ $tier->name }}<input type="hidden" name="areas[{{ $i }}][size_tier_id]" value="{{ $tier->id }}"></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="areas[{{ $i }}][panjang]" value="{{ old("areas.$i.panjang", $existing->panjang ?? 0) }}" required></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="areas[{{ $i }}][lebar]" value="{{ old("areas.$i.lebar", $existing->lebar ?? 0) }}" required></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p class="text-muted small">Area is computed automatically as Panjang × Lebar.</p>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.rooms.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($room) ? 'Update Room' : 'Save Room' }}</button>
        </div>
    </form>
</div></div></div></div>
@endsection
