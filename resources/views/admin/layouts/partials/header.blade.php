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
        <li class="pc-h-item d-none d-md-flex align-items-center me-2">
          <div class="admin-search">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-transparent"><i class="ph-duotone ph-magnifying-glass"></i></span>
              <input type="text" id="admin-search" class="form-control" placeholder="Search projects, team…" autocomplete="off" aria-label="Global search">
            </div>
            <div id="admin-search-results" class="admin-search-results"></div>
          </div>
        </li>
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

@push('scripts')
<script>
(function () {
    var input = document.getElementById('admin-search');
    var box = document.getElementById('admin-search-results');
    if (!input || !box) return;
    var url = "{{ route('admin.search') }}";
    var timer = null;

    function hide() { box.classList.remove('show'); box.innerHTML = ''; }
    function esc(s) { var d = document.createElement('div'); d.textContent = (s == null ? '' : s); return d.innerHTML; }

    function render(results, q) {
        if (!results.length) {
            box.innerHTML = '<div class="dropdown-item-text text-muted small">No matches for &ldquo;' + esc(q) + '&rdquo;</div>';
            box.classList.add('show');
            return;
        }
        box.innerHTML = results.map(function (r) {
            return '<a class="dropdown-item d-flex align-items-center gap-2 py-2" href="' + r.url + '">' +
                '<i class="ph-duotone ' + esc(r.icon) + ' f-18 text-muted"></i>' +
                '<span class="flex-grow-1 text-truncate">' + esc(r.label) +
                '<small class="d-block text-muted text-truncate">' + esc(r.sublabel || '') + '</small></span>' +
                '<span class="badge bg-light-secondary text-secondary">' + esc(r.type) + '</span>' +
            '</a>';
        }).join('');
        box.classList.add('show');
    }

    function run() {
        var q = input.value.trim();
        if (q.length < 2) { hide(); return; }
        fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { render(data.results || [], q); })
            .catch(function () { hide(); });
    }

    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(run, 250); });
    input.addEventListener('focus', function () { if (input.value.trim().length >= 2) run(); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { hide(); input.blur(); }
        else if (e.key === 'Enter') {
            var first = box.querySelector('a.dropdown-item');
            if (first) { e.preventDefault(); window.location.href = first.getAttribute('href'); }
        }
    });
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !box.contains(e.target)) hide();
    });
})();
</script>
@endpush
