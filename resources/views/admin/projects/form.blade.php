@extends('admin.layouts.admin')

@section('title', isset($project) ? 'Edit Project' : 'Create Project')
@section('page-title', isset($project) ? 'Edit Project' : 'Create Project')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($project) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ isset($project) ? 'Edit Project Details' : 'New Project Configuration' }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($project) ? route('admin.projects.update', $project->id) : route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($project))
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="title">Project Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" 
                                   value="{{ old('title', $project->title ?? '') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="slug">URL Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" 
                                   value="{{ old('slug', $project->slug ?? '') }}" required readonly style="background-color: #f8f9fa;">
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
                            <input type="text" class="form-control @error('status') is-invalid @enderror" id="status" name="status" 
                                   value="{{ old('status', $project->status ?? '') }}" placeholder="e.g. Completed, Under Construction">
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="architect">Architect</label>
                            <input type="text" class="form-control @error('architect') is-invalid @enderror" id="architect" name="architect" 
                                   value="{{ old('architect', $project->architect ?? '') }}" list="architectList" placeholder="Select or type a name">
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
                                   value="{{ old('floor_area', $project->floor_area ?? '') }}">
                            @error('floor_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="site_area">Site Area</label>
                            <input type="text" class="form-control @error('site_area') is-invalid @enderror" id="site_area" name="site_area" 
                                   value="{{ old('site_area', $project->site_area ?? '') }}">
                            @error('site_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="stories">Stories</label>
                            <input type="text" class="form-control @error('stories') is-invalid @enderror" id="stories" name="stories" 
                                   value="{{ old('stories', $project->stories ?? '') }}">
                            @error('stories')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mb-4">
                            <label class="form-label" for="location">Location</label>
                            <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="3">{{ old('location', $project->location ?? '') }}</textarea>
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Project Media & Content</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-content-btn">
                            <i class="ti ti-plus"></i> Add Media Item
                        </button>
                    </div>

                    <div id="contents-wrapper">
                        @if(isset($project) && $project->contents->count() > 0)
                            @foreach($project->contents as $index => $content)
                            <div class="content-item card bg-light-primary border-0 mb-3" data-index="{{ $index }}">
                                <div class="card-body p-3 relative">
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-content-btn position-absolute top-0 end-0 m-2"><i class="ti ti-trash"></i></button>
                                    
                                    <input type="hidden" name="contents[{{ $index }}][id]" value="{{ $content->id }}">
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label text-xs">Current Image</label>
                                            <img src="{{ asset('storage/' . $content->image_url) }}" class="img-fluid rounded border mb-2" style="max-height: 100px; width: auto; display: block;">
                                            <label class="form-label text-xs">Replace Image</label>
                                            <input type="file" class="form-control form-control-sm" name="contents[{{ $index }}][image]" accept="image/*">
                                        </div>
                                        <div class="col-md-7 mb-3">
                                            <label class="form-label">Description (Optional)</label>
                                            <textarea class="form-control" name="contents[{{ $index }}][description]" rows="4">{{ $content->description }}</textarea>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label">Sort Order</label>
                                            <input type="number" class="form-control" name="contents[{{ $index }}][order]" value="{{ $content->order }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ isset($project) ? 'Update Project' : 'Save Project' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');

        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function() {
                let text = titleInput.value;
                let slug = text.toString().toLowerCase()
                    .replace(/\s+/g, '-')           
                    .replace(/[^\w\-]+/g, '')       
                    .replace(/\-\-+/g, '-')         
                    .replace(/^-+/, '')             
                    .replace(/-+$/, '');            
                
                slugInput.value = slug;
            });
        }

        let contentIndex = {{ isset($project) ? $project->contents->count() : 0 }};
        const contentsWrapper = document.getElementById('contents-wrapper');
        const addContentBtn = document.getElementById('add-content-btn');

        addContentBtn.addEventListener('click', function() {
            const template = `
                <div class="content-item card bg-light-secondary border-0 mb-3" data-index="${contentIndex}">
                    <div class="card-body p-3 position-relative">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-content-btn position-absolute top-0 end-0 m-2"><i class="ti ti-trash"></i></button>
                        
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Upload Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="contents[${contentIndex}][image]" accept="image/*" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea class="form-control" name="contents[${contentIndex}][description]" rows="3"></textarea>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Order</label>
                                <input type="number" class="form-control" name="contents[${contentIndex}][order]" value="${contentIndex + 1}">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            contentsWrapper.insertAdjacentHTML('beforeend', template);
            contentIndex++;
        });

        contentsWrapper.addEventListener('click', function(e) {
            if (e.target.closest('.remove-content-btn')) {
                const btn = e.target.closest('.remove-content-btn');
                const item = btn.closest('.content-item');
                
                if (item.querySelector('input[name*="[id]"]')) {
                    // Mark for deletion if it exists in DB
                    const idInput = item.querySelector('input[name*="[id]"]');
                    idInput.name = `deleted_contents[]`;
                    item.style.display = 'none';
                    item.classList.remove('content-item');
                } else {
                    item.remove();
                }
            }
        });
    });
</script>
@endpush
