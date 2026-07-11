<header class="pc-header">
  <div class="header-wrapper">
    <!-- [Mobile Media Block] start -->
    <div class="me-auto pc-mob-drp">
      <ul class="list-unstyled">
        <!-- ======= Menu collapse Icon ===== -->
        <li class="pc-h-item pc-sidebar-collapse">
          <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
        <li class="pc-h-item pc-sidebar-popup">
          <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
      </ul>
    </div>
    <!-- [Mobile Media Block end] -->

    <div class="ms-auto">
      <ul class="list-unstyled d-flex align-items-center mb-0">
        <li class="pc-h-item">
          <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-sm btn-light-primary d-flex align-items-center gap-1 me-2" title="Open the public website in a new tab">
            <i class="ph-duotone ph-globe"></i>
            <span class="d-none d-sm-inline">View Site</span>
          </a>
        </li>
        <li class="dropdown pc-h-item header-user-profile">
          <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
            <i class="ti ti-user user-avtar wid-35"></i>
          </a>
          <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
            <div class="dropdown-header d-flex align-items-center justify-content-between">
              <h5 class="m-0">Profile</h5>
            </div>
            <div class="dropdown-body">
              <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                <div class="d-flex mb-1">
                  <div class="flex-shrink-0">
                     <!-- <i class="ti ti-user user-avtar wid-35"></i> -->
                    <!-- <img src="{{ asset('admin_assets/images/user/avatar-2.jpg') }}" alt="user-image" class="user-avtar wid-35"> -->
                  </div>
                  <div class="flex-grow-1 ms-3 pt-3">
                    <h6 class="mb-1">{{ auth()->user()->name ?? 'Administrator' }} </h6>
                    <span>{{ auth()->user()->email ?? 'admin@farkastudio.com' }}</span>
                  </div>
                </div>
                <hr class="border-secondary border-opacity-50">
                <a href="{{ route('admin.profile.edit') }}" class="dropdown-item">
                  <span>
                    <i class="ph-duotone ph-user-gear"></i>
                    <span>My Profile</span>
                  </span>
                </a>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="dropdown-item">
                  <span>
                    <i class="ph-duotone ph-globe"></i>
                    <span>Visit Website</span>
                  </span>
                </a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
                  <span>
                    <i class="ph-duotone ph-power"></i>
                    <span>Logout</span>
                  </span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</header>
