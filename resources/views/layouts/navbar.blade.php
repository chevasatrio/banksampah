<header class="navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="page-title">
            <h2>@yield('page-title', 'Dashboard')</h2>
            <p class="breadcrumb-text">@yield('breadcrumb', '')</p>
        </div>
    </div>
    <div class="navbar-right">
        <div class="navbar-date">
            <i class="far fa-calendar-alt"></i>
            <span>{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>
</header>
