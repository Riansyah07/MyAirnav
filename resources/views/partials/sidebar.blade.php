
<!-- Sidebar -->
<div class="main-sidebar sidebar-style-2">
  @php
          if (Auth::check()) {
              if (Auth::user()->role === 'superadmin') {
                  $dashboardRoute = route('superadmin.dashboard');
                  $documentsMainRoute = route('superadmin.documents.index');
                  $documentsRoutePrefix = 'superadmin.documents.category';
              } elseif (Auth::user()->role === 'admin') {
                  $dashboardRoute = route('admin.dashboard');
                  $documentsMainRoute = route('admin.documents.index');
                  $documentsRoutePrefix = 'admin.documents.category';
              } else {
                  $dashboardRoute = route('user.dashboard');
                  $documentsMainRoute = route('user.documents.index');
                  $documentsRoutePrefix = 'user.documents.category';
              }
          } else {
              $dashboardRoute = '#';
              $documentsMainRoute = '#';
              $documentsRoutePrefix = '#';
          }
      @endphp
  <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
          <a href="#">MY AIRNAV</a>
      </div>
      <div class="sidebar-brand sidebar-brand-sm">
          <a href="#">MA</a>
      </div>
      <ul class="sidebar-menu">
          <li class="menu-header">Pilihan Menu</li>
          <li><a href="{{ route('dashboard') }}"><i class="fas fa-archway"></i> <span>Dashboard</span></a></li>
          <li class="dropdown">
              <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Pilih Dokumen</span></a>
              <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="{{ $documentsMainRoute }}">Semua Dokumen</a></li>
                  <li><a class="dropdown-item" href="{{ route($documentsRoutePrefix, ['category' => 'teknik']) }}">Teknik</a></li>
                  <li><a class="dropdown-item" href="{{ route($documentsRoutePrefix, ['category' => 'operasi']) }}">Operasi</a></li>
                  <li><a class="dropdown-item" href="{{ route($documentsRoutePrefix, ['category' => 'k3']) }}">K3</a></li>
              </ul>
          </li>

                    <!-- Tombol Kelola User (Hanya untuk Superadmin) -->
            @auth
            @if(Auth::user()->role === 'superadmin')
                <li>
                    <a href="{{ route('superadmin.users.index') }}">
                        <i class="far fa-user"></i> <span>Kelola User</span>
                    </a>
                </li>
            @endif
            @endauth
      </ul>
  </aside>
</div>