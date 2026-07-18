<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="{{ route('admin.dashboard') }}" class="b-brand text-primary">
        <!-- <img src="{{ asset('assets/images/farkalogo.svg') }}" alt="logo image" class="logo-lg" style="max-height: 40px;"> -->
        <span class="badge bg-brand-color-2 rounded-pill ms-2 theme-version">Admin Panel</span>
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item pc-caption">
          <label>Navigation</label>
          <i class="ph-duotone ph-gauge"></i>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <a href="{{ route('admin.dashboard') }}" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-gauge"></i></span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>Content Management</label>
          <i class="ph-duotone ph-buildings"></i>
        </li>
        @can('view_categories')
        <li class="pc-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
          <a href="{{ route('admin.categories.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-squares-four"></i></span>
            <span class="pc-mtext">Categories</span>
          </a>
        </li>
        @endcan
        @can('view_projects')
        <li class="pc-item {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
          <a href="{{ route('admin.projects.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-briefcase"></i></span>
            <span class="pc-mtext">Projects</span>
          </a>
        </li>
        @endcan


        <li class="pc-item pc-caption">
          <label>About Settings</label>
          <i class="ph-duotone ph-users"></i>
        </li>
        @can('view_key_people')
        <li class="pc-item {{ request()->routeIs('admin.key-people.*') ? 'active' : '' }}">
          <a href="{{ route('admin.key-people.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-identification-badge"></i></span>
            <span class="pc-mtext">Team Members</span>
          </a>
        </li>
        @endcan
        @can('view_page_settings')
        <li class="pc-item {{ request()->routeIs('admin.contact-settings.*') ? 'active' : '' }}">
          <a href="{{ route('admin.contact-settings.edit') }}" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-address-book"></i></span>
            <span class="pc-mtext">Page Settings</span>
          </a>
        </li>
        @endcan

        @can('view_calculator')
        <li class="pc-item pc-caption">
          <label>Budget Calculator</label>
          <i class="ph-duotone ph-calculator"></i>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.rooms.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.rooms.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-door"></i></span><span class="pc-mtext">Rooms &amp; Areas</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.zonasi.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.zonasi.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-map-trifold"></i></span><span class="pc-mtext">Zonasi</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.building-types.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.building-types.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-buildings"></i></span><span class="pc-mtext">Building Types &amp; Price</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.factor-groups.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.factor-groups.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-sliders"></i></span><span class="pc-mtext">Weighting Factors</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.allocations.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.allocations.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-chart-pie-slice"></i></span><span class="pc-mtext">Allocations</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.components.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.components.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-squares-four"></i></span><span class="pc-mtext">Quality Components</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.size-tiers.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.size-tiers.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-ruler"></i></span><span class="pc-mtext">Size Tiers</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.settings.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.settings.edit') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-gear-six"></i></span><span class="pc-mtext">Calculator Settings</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('kalkulator.show') ? 'active' : '' }}">
          <a href="{{ route('kalkulator.show') }}" target="_blank" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-calculator"></i></span><span class="pc-mtext">Open Calculator ↗</span></a>
        </li>
        @endcan

        @role('super_admin')
        <li class="pc-item pc-caption">
          <label>System</label>
          <i class="ph-duotone ph-gear"></i>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
          <a href="{{ route('admin.roles.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-shield-check"></i></span>
            <span class="pc-mtext">Roles & Permissions</span>
          </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <a href="{{ route('admin.users.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-user-circle"></i></span>
            <span class="pc-mtext">Users Management</span>
          </a>
        </li>
        @endrole
      </ul>
      
      <div class="card nav-action-card bg-brand-color-4 mt-4">
        <div class="card-body" style="background-image: url('{{ asset("admin_assets/images/layout/nav-card-bg.svg") }}')">
          <h5 class="text-dark">Need Support?</h5>
          <p class="text-dark text-opacity-75">Contact developer for assistance.</p>
        </div>
      </div>
    </div>
  </div>
</nav>
