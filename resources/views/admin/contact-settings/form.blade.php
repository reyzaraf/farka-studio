@extends('admin.layouts.admin')

@section('title', 'Contact Settings')
@section('page-title', 'Company Contact Information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item" aria-current="page">Contact Settings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>Update Global Contact Details</h5>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <form action="{{ route('admin.contact-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label" for="email">Display Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                               value="{{ old('email', $setting->email ?? '') }}" placeholder="e.g. hello@farkastudio.test">
                        <small class="form-text text-muted">This email is shown publicly on the site.</small>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="phone">Contact Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" 
                               value="{{ old('phone', $setting->phone ?? '') }}" placeholder="e.g. +62 812 3456 7890">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5">
                        <label class="form-label" for="address">Studio Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" 
                                  rows="4" placeholder="Enter full physical address">{{ old('address', $setting->address ?? '') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5">
                        <label class="form-label" for="video_url">Background Video URL</label>
                        <input type="url" class="form-control @error('video_url') is-invalid @enderror" id="video_url" name="video_url" 
                               value="{{ old('video_url', $setting->video_url ?? '') }}" placeholder="e.g. https://www.pexels.com/download/video/36168059/" oninput="updateVideoPreview()">
                        <small class="form-text text-muted">Supports: direct MP4 link, YouTube URL, or Google Drive share link (e.g. <code>https://drive.google.com/file/d/FILE_ID/view</code>).</small>
                        @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        
                        <!-- Video Preview -->
                        <div class="mt-3">
                            <label class="form-label">Video Preview</label>
                            <div id="previewContainer" class="rounded overflow-hidden bg-dark position-relative" style="height: 250px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">
                                <span id="noVideoText" class="text-white position-absolute">No Video Selected</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateVideoPreview() {
        const input = document.getElementById('video_url');
        const container = document.getElementById('previewContainer');
        const val = input.value.trim();

        if (val !== "") {
            const ytRegex = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i;
            const gdRegex = /drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/i;
            const ytMatch = val.match(ytRegex);
            const gdMatch = val.match(gdRegex);

            if (ytMatch && ytMatch[1]) {
                const ytId = ytMatch[1];
                container.innerHTML = `<iframe src="https://www.youtube.com/embed/${ytId}?autoplay=1&mute=1&loop=1&playlist=${ytId}" class="w-100 h-100" style="border:0;" allow="autoplay; fullscreen"></iframe>`;
            } else if (gdMatch && gdMatch[1]) {
                const gdId = gdMatch[1];
                container.innerHTML = `<iframe src="https://drive.google.com/file/d/${gdId}/preview" class="w-100 h-100" style="border:0;" allow="autoplay; fullscreen"></iframe>`;
            } else {
                container.innerHTML = `<video src="${val}" autoplay muted loop playsinline class="w-100 h-100" style="object-fit: cover;"></video>`;
            }
        } else {
            container.innerHTML = `<span id="noVideoText" class="text-white position-absolute">No Video Selected</span>`;
        }
    }
    
    document.addEventListener('DOMContentLoaded', updateVideoPreview);
</script>
@endsection
