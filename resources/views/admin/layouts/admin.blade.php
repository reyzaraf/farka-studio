<!doctype html>
<html lang="en">
  <!-- [Head] start -->
  <head>
    <title>@yield('title', 'Admin Panel') | Farka Studio</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('farkalogo.svg') }}" type="image/x-icon">

    <!-- [Google Font : Public Sans] icon -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- [phosphor Icons] https://phosphoricons.com/ -->
    <link rel="stylesheet" href="{{ asset('admin_assets/fonts/phosphor/duotone/style.css') }}">
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="{{ asset('admin_assets/fonts/tabler-icons.min.css') }}">
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="{{ asset('admin_assets/fonts/feather.css') }}">
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="{{ asset('admin_assets/fonts/fontawesome.css') }}">
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="{{ asset('admin_assets/fonts/material.css') }}">
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style-preset.css') }}">
    <!-- [Custom admin styles] -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/admin-custom.css') }}">

    @stack('styles')
  </head>
  <!-- [Head] end -->
  
  <!-- [Body] Start -->
  <body data-pc-preset="preset-1" data-pc-sidebar-theme="dark" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ Sidebar Menu ] start -->
    @include('admin.layouts.partials.sidebar')
    <!-- [ Sidebar Menu ] end -->

    <!-- [ Header Topbar ] start -->
    @include('admin.layouts.partials.header')
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pc-container">
      <div class="pc-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header">
          <div class="page-block">
            <div class="row align-items-center">
              <div class="col-md-12">
                <ul class="breadcrumb">
                  @hasSection('breadcrumb')
                    @yield('breadcrumb')
                  @else
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                  @endif
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  {{-- Views set either @section('page_title') or @section('page-title'); render whichever is present --}}
                  <h2 class="mb-0">@yield('page_title')@yield('page-title')</h2>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- [ breadcrumb ] end -->
        
        <!-- [ Global flash + validation feedback ] start -->
        <div id="app-flash">
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="ti ti-circle-check me-1"></i>{{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="ti ti-alert-circle me-1"></i>{{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <strong><i class="ti ti-alert-triangle me-1"></i>Please fix the following {{ $errors->count() }} issue(s):</strong>
              <ul class="mb-0 mt-2 ps-3">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </div>
        <!-- [ Global flash + validation feedback ] end -->

        <!-- [ Main Content ] start -->
        @yield('content')
        <!-- [ Main Content ] end -->
      </div>
    </div>
    <!-- [ Main Content ] end -->

    <footer class="pc-footer">
      <div class="footer-wrapper container-fluid">
        <div class="row">
          <div class="col-sm-6 my-1">
            <p class="m-0">Copyright &copy; {{ date('Y') }} Farka Studio. All rights reserved.</p>
          </div>
        </div>
      </div>
    </footer>

    <!-- Required Js -->
    <script src="{{ asset('admin_assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('admin_assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin_assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('admin_assets/js/icon/custom-font.js') }}"></script>
    <script src="{{ asset('admin_assets/js/script.js') }}"></script>
    <script src="{{ asset('admin_assets/js/theme.js') }}"></script>
    <script src="{{ asset('admin_assets/js/plugins/feather.min.js') }}"></script>

    <script>
      layout_change('light');
      layout_sidebar_change('dark');
      change_box_container('false');
      layout_caption_change('true');
      layout_rtl_change('false');
      preset_change('preset-1');
    </script>
    
    <!-- jQuery (self-hosted) — loaded once here so DataTables/plugins in @stack('scripts') work even offline -->
    <script src="{{ asset('admin_assets/js/plugins/jquery.min.js') }}"></script>
    <!-- SweetAlert2 for Delete Confirmations (self-hosted) -->
    <script src="{{ asset('admin_assets/js/plugins/sweetalert2.min.js') }}"></script>

    <!-- Shared, dependency-free delete confirmation + flash helper -->
    <script>
      // Delegated so it works for any .delete-btn (including rows re-rendered by DataTables).
      // Falls back to native confirm() if SweetAlert failed to load, so delete never silently no-ops.
      document.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-btn');
        if (!btn) return;
        e.preventDefault();
        const form = btn.closest('form');
        if (!form) return;
        const name = btn.getAttribute('data-name');
        const message = name
          ? 'Delete "' + name + '"? This action cannot be undone.'
          : "You won't be able to revert this!";
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
          }).then((result) => { if (result.isConfirmed) form.submit(); });
        } else if (window.confirm(message)) {
          form.submit();
        }
      });

      // Bring server feedback into view and auto-dismiss success alerts.
      document.addEventListener('DOMContentLoaded', function () {
        const flash = document.getElementById('app-flash');
        if (flash && flash.querySelector('.alert')) {
          flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          flash.querySelectorAll('.alert-success').forEach(function (el) {
            setTimeout(function () {
              if (window.bootstrap && bootstrap.Alert) { bootstrap.Alert.getOrCreateInstance(el).close(); }
            }, 5000);
          });
        }
      });
    </script>

    @stack('scripts')
  </body>
  <!-- [Body] end -->
</html>
