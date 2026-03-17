<!doctype html>
<html lang="en">
  <head>
    <title>Confirm Password | Admin Farka Studio</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('assets/farkalogo.svg') }}" type="image/x-icon">
    <!-- [Google Font : Public Sans] icon -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style-preset.css') }}">
  </head>

  <body data-pc-preset="preset-1" data-pc-sidebar-theme="light" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <div class="auth-main v1">
      <div class="auth-wrapper">
        <div class="auth-form">
          <div class="card my-5">
            <div class="card-body">
              <div class="text-center">
                <img src="{{ asset('assets/farkalogo.svg') }}" alt="logo" class="img-fluid mb-4" style="max-height: 80px;">
                <h4 class="f-w-500 mb-4">Confirm Password</h4>
                <p class="text-muted mb-4">Please confirm your password before continuing.</p>
              </div>

              <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div class="mb-3">
                  <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password" required autocomplete="current-password" autofocus>
                  @error('password')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
                </div>

                <div class="d-grid mt-4">
                  <button type="submit" class="btn btn-primary">Confirm Password</button>
                </div>
                
                @if (Route::has('password.request'))
                <div class="d-flex mt-4 justify-content-center">
                  <a href="{{ route('password.request') }}" class="text-secondary">Forgot Your Password?</a>
                </div>
                @endif
              </form>
            </div>
          </div>
        </div>
        <div class="auth-sidefooter">
          <img src="{{ asset('assets/farkalogo.svg') }}" class="img-brand img-fluid" alt="images" style="max-height: 40px;">
          <hr class="mb-3 mt-4">
          <div class="row">
            <div class="col my-1">
              <p class="m-0">Copyright &copy; {{ date('Y') }} Farka Studio. All rights reserved.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Required Js -->
    <script src="{{ asset('admin_assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('admin_assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin_assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('admin_assets/js/script.js') }}"></script>
    <script src="{{ asset('admin_assets/js/theme.js') }}"></script>

    <script>
      layout_change('light');
      preset_change('preset-1');
    </script>
  </body>
</html>
