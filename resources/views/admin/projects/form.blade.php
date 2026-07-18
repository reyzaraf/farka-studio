@extends('admin.layouts.admin')

@section('title', isset($project) ? 'Edit Project' : 'Create Project')
@section('page_title', isset($project) ? 'Edit Project' : 'Create Project')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($project) ? 'Edit' : 'Create' }}</li>
@endsection

@php
    // The fixed set of statuses shown as a public badge. Adjust here to change the options.
    $statusOptions = ['Completed', 'Under Construction', 'Concept'];
    $currentStatus = old('status', $project->status ?? '');

    // Build the media rows to render, preserving operator input when validation fails (old('contents')).
    $existingById = isset($project) ? $project->contents->keyBy('id') : collect();
    if (old('contents') !== null) {
        $mediaRows = collect(old('contents'))->map(function ($row) use ($existingById) {
            $id = $row['id'] ?? null;
            return [
                'id'          => $id,
                'image_url'   => ($id && $existingById->has($id)) ? $existingById[$id]->image_url : null,
                'description' => $row['description'] ?? '',
                'order'       => $row['order'] ?? 0,
            ];
        })->values();
    } elseif (isset($project)) {
        $mediaRows = $project->contents->map(fn ($c) => [
            'id'          => $c->id,
            'image_url'   => $c->image_url,
            'description' => $c->description,
            'order'       => $c->order,
        ])->values();
    } else {
        $mediaRows = collect();
    }
    $nextMediaOrder = ($mediaRows->max('order') ?? 0) + 1;
@endphp

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ isset($project) ? 'Edit Project Details' : 'New Project Configuration' }}</h5>
            </div>
            <div class="card-body">
                <form id="project-form" action="{{ isset($project) ? route('admin.projects.update', $project->id) : route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($project))
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="title">Project Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                                   value="{{ old('title', $project->title ?? '') }}" maxlength="255" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label d-flex justify-content-between align-items-center" for="slug">
                                <span>URL Slug <span class="text-danger">*</span></span>
                                <button type="button" class="btn btn-link btn-sm p-0" id="edit-slug-btn"><i class="ti ti-pencil"></i> Edit URL</button>
                            </label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug"
                                   value="{{ old('slug', $project->slug ?? '') }}" maxlength="255" required readonly style="background-color: #f8f9fa;">
                            <small class="form-text text-muted">Auto-generated from the title. Click <strong>Edit URL</strong> to customise or resolve a duplicate.</small>
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="category_id">Category</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('category_id', $project->category_id ?? '')) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="">— Select status —</option>
                                @foreach($statusOptions as $opt)
                                    <option value="{{ $opt }}" {{ $currentStatus === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                                {{-- Preserve any existing free-text value not in the fixed list so it is never silently lost --}}
                                @if($currentStatus && !in_array($currentStatus, $statusOptions))
                                    <option value="{{ $currentStatus }}" selected>{{ $currentStatus }} (current)</option>
                                @endif
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="architect">Architect</label>
                            <input type="text" class="form-control @error('architect') is-invalid @enderror" id="architect" name="architect"
                                   value="{{ old('architect', $project->architect ?? '') }}" list="architectList" maxlength="255" placeholder="Select or type a name">
                            <datalist id="architectList">
                                @if(isset($architects))
                                    @foreach($architects as $name)
                                        <option value="{{ $name }}">
                                    @endforeach
                                @endif
                            </datalist>
                            @error('architect')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="floor_area">Floor Area</label>
                            <input type="text" class="form-control @error('floor_area') is-invalid @enderror" id="floor_area" name="floor_area"
                                   value="{{ old('floor_area', $project->floor_area ?? '') }}" maxlength="255" placeholder="e.g. 200 m²">
                            @error('floor_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="site_area">Site Area</label>
                            <input type="text" class="form-control @error('site_area') is-invalid @enderror" id="site_area" name="site_area"
                                   value="{{ old('site_area', $project->site_area ?? '') }}" maxlength="255" placeholder="e.g. 350 m²">
                            @error('site_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="stories">Stories</label>
                            <input type="text" class="form-control @error('stories') is-invalid @enderror" id="stories" name="stories"
                                   value="{{ old('stories', $project->stories ?? '') }}" maxlength="255" placeholder="e.g. 2 floors">
                            @error('stories')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="order">Project Order (Lower appears first)</label>
                            <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order"
                                   value="{{ old('order', $project->order ?? 0) }}">
                            @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label d-block">Visibility</label>
                            <div class="form-check form-switch mt-2">
                                {{-- Hidden 0 first so an unchecked switch still submits a value (and survives validation errors) --}}
                                <input type="hidden" name="is_published" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_published" name="is_published" value="1"
                                       {{ old('is_published', $project->is_published ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Published (visible on the public site)</label>
                            </div>
                            <small class="form-text text-muted">Turn off to keep this project as a private draft while you build it.</small>
                        </div>

                        <div class="col-12 mb-4">
                            <label class="form-label" for="location">Location</label>
                            <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="3">{{ old('location', $project->location ?? '') }}</textarea>
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">Project Media &amp; Content</h5>
                            <small class="text-muted d-block">Drag the <i class="ti ti-grip-vertical"></i> handle to reorder. The first image is used as the cover.</small>
                            <small class="text-primary d-block mt-1"><i class="ti ti-photo"></i> Recommended image size: <strong>landscape 3:2</strong> — e.g. <strong>1600&times;1067px</strong> (min. 1200px wide). Keep every image in a project the same orientation for a tidy gallery. Format: JPG / PNG / WebP.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-content-btn">
                            <i class="ti ti-plus"></i> Add Media Item
                        </button>
                    </div>

                    <div id="contents-wrapper">
                        @foreach($mediaRows as $index => $content)
                            <div class="content-item card bg-light-primary border-0 mb-3" data-index="{{ $index }}">
                                <div class="card-body p-3 position-relative">
                                    <span class="drag-handle position-absolute top-0 start-0 m-2 text-muted" title="Drag to reorder"><i class="ti ti-grip-vertical"></i></span>
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-content-btn position-absolute top-0 end-0 m-2"><i class="ti ti-trash"></i></button>

                                    @if($content['id'])
                                        <input type="hidden" name="contents[{{ $index }}][id]" value="{{ $content['id'] }}">
                                    @endif

                                    <div class="row mt-3">
                                        <div class="col-md-3 mb-3">
                                            @if($content['image_url'])
                                                <label class="form-label text-xs">Current Image</label>
                                                <img src="{{ asset('storage/' . $content['image_url']) }}" class="img-fluid rounded border mb-2" style="max-height: 100px; width: auto; display: block;">
                                                <label class="form-label text-xs">Replace Image</label>
                                            @else
                                                <label class="form-label text-xs">Upload Image</label>
                                                @if(old('contents') !== null)
                                                    <div class="alert alert-warning py-1 px-2 small mb-2">Please re-select this image (files can't be restored after an error).</div>
                                                @endif
                                            @endif
                                            <img class="media-preview mb-2">
                                            <input type="file" class="form-control form-control-sm media-file" name="contents[{{ $index }}][image]" accept="image/*">
                                            <small class="text-muted d-block mt-1">Max upload size: 35MB</small>
                                        </div>
                                        <div class="col-md-7 mb-3">
                                            <label class="form-label">Description (Optional)</label>
                                            <textarea class="form-control" name="contents[{{ $index }}][description]" rows="4">{{ $content['description'] }}</textarea>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label">Sort Order</label>
                                            <input type="number" class="form-control content-order" name="contents[{{ $index }}][order]" value="{{ $content['order'] }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Preserve deletions across a validation failure --}}
                    @foreach((array) old('deleted_contents', []) as $delId)
                        <input type="hidden" name="deleted_contents[]" value="{{ $delId }}">
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">Cancel</a>
                        <div class="d-flex gap-2">
                            @if(isset($project))
                                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-light-info"><i class="ti ti-external-link"></i> View Site</a>
                            @endif
                            <button type="submit" class="btn btn-primary" id="submit-btn">{{ isset($project) ? 'Update Project' : 'Save Project' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin_assets/js/plugins/sortable.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const MAX_BYTES = 35 * 1024 * 1024;
        const form = document.getElementById('project-form');
        const isEdit = {{ isset($project) ? 'true' : 'false' }};

        /* ---------- Slug: auto-generate on create, lockable everywhere ---------- */
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        const editSlugBtn = document.getElementById('edit-slug-btn');
        // On edit: never auto-rewrite. On create after a validation error where the slug was
        // hand-edited (differs from the slugified title): treat it as manual so it isn't clobbered.
        let slugManual = isEdit || {{ (old('slug') && old('slug') !== \Illuminate\Support\Str::slug(old('title', ''))) ? 'true' : 'false' }};

        function slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        }
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function () {
                if (!slugManual) slugInput.value = slugify(titleInput.value);
            });
        }
        if (editSlugBtn && slugInput) {
            editSlugBtn.addEventListener('click', function () {
                slugManual = true;
                slugInput.removeAttribute('readonly');
                slugInput.style.backgroundColor = '';
                slugInput.focus();
            });
        }

        /* ---------- Media rows: add / remove / preview / reorder ---------- */
        const contentsWrapper = document.getElementById('contents-wrapper');
        const addContentBtn = document.getElementById('add-content-btn');
        let contentIndex = {{ $mediaRows->count() }};
        let nextOrder = {{ $nextMediaOrder }};

        addContentBtn.addEventListener('click', function () {
            const i = contentIndex;
            const template = `
                <div class="content-item card bg-light-secondary border-0 mb-3" data-index="${i}">
                    <div class="card-body p-3 position-relative">
                        <span class="drag-handle position-absolute top-0 start-0 m-2 text-muted" title="Drag to reorder"><i class="ti ti-grip-vertical"></i></span>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-content-btn position-absolute top-0 end-0 m-2"><i class="ti ti-trash"></i></button>
                        <div class="row mt-3">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Upload Image</label>
                                <img class="media-preview mb-2">
                                <input type="file" class="form-control media-file" name="contents[${i}][image]" accept="image/*">
                                <small class="text-muted d-block mt-1">Max upload size: 35MB</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea class="form-control" name="contents[${i}][description]" rows="3"></textarea>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Order</label>
                                <input type="number" class="form-control content-order" name="contents[${i}][order]" value="${nextOrder}">
                            </div>
                        </div>
                    </div>
                </div>`;
            contentsWrapper.insertAdjacentHTML('beforeend', template);
            contentIndex++;
            nextOrder++;
            markDirty();
        });

        // Delegated: remove row (soft-delete existing rows via deleted_contents[])
        contentsWrapper.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-content-btn');
            if (!btn) return;
            const item = btn.closest('.content-item');
            const idInput = item.querySelector('input[name*="[id]"]');
            if (idInput) {
                idInput.name = 'deleted_contents[]';
                item.style.display = 'none';
                item.classList.remove('content-item');
            } else {
                item.remove();
            }
            // Note: don't renumber here — that would overwrite the operator's typed Sort Order values.
            // Ordering is renumbered only on an explicit drag (onEnd below).
            markDirty();
        });

        // Delegated: live image preview + oversize warning on file pick
        contentsWrapper.addEventListener('change', function (e) {
            const input = e.target.closest('.media-file');
            if (!input) return;
            const file = input.files && input.files[0];
            const preview = input.closest('.col-md-3').querySelector('.media-preview');
            if (!file) { if (preview) preview.classList.remove('is-shown'); return; }
            if (preview) {
                preview.src = URL.createObjectURL(file);
                preview.classList.add('is-shown');
            }
            input.classList.toggle('is-invalid', file.size > MAX_BYTES);
            let warn = input.parentElement.querySelector('.size-warning');
            if (file.size > MAX_BYTES) {
                if (!warn) {
                    warn = document.createElement('div');
                    warn.className = 'size-warning text-danger small mt-1';
                    input.after(warn);
                }
                warn.textContent = 'This file is ' + (file.size / 1048576).toFixed(1) + ' MB — over the 35MB limit.';
            } else if (warn) {
                warn.remove();
            }
        });

        // Renumber the hidden order inputs to match visual (drag) order
        function renumberOrders() {
            let n = 1;
            contentsWrapper.querySelectorAll('.content-item .content-order').forEach(function (inp) {
                inp.value = n++;
            });
        }

        // Drag-and-drop reordering
        if (window.Sortable) {
            Sortable.create(contentsWrapper, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function () { renumberOrders(); markDirty(); }
            });
        }

        /* ---------- Unsaved-changes guard + saving state ---------- */
        let dirty = false;
        let submitting = false;
        function markDirty() { dirty = true; }
        if (form) {
            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
            form.addEventListener('submit', function () {
                submitting = true;
                const btn = document.getElementById('submit-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
                }
            });
        }
        window.addEventListener('beforeunload', function (e) {
            if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; }
        });
    });
</script>
@endpush
