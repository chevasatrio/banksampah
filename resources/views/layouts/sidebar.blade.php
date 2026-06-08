<div class="sidebar-overlay"></div>
<aside class="sidebar">
    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-recycle"></i>
        </div>
        <div class="brand-text">
            <h1>SIBANK</h1>
            <p>Bank Sampah Digital</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        @if(auth()->user()->isAdmin() || auth()->user()->isPetugas())
            {{-- Dashboard --}}
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
            @endif

            {{-- Nasabah --}}
            <div class="nav-section">Data Master</div>
            <a href="{{ route('admin.nasabah.index') }}"
               class="nav-item {{ request()->routeIs('admin.nasabah.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Nasabah</span>
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.kategori-sampah.index') }}"
               class="nav-item {{ request()->routeIs('admin.kategori-sampah.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>Kategori Sampah</span>
            </a>
            @endif

            <a href="{{ route('admin.jenis-sampah.index') }}"
               class="nav-item {{ request()->routeIs('admin.jenis-sampah.*') ? 'active' : '' }}">
                <i class="fas fa-trash-alt"></i>
                <span>Jenis & Harga</span>
            </a>

            {{-- Transaksi --}}
            <div class="nav-section">Transaksi</div>
            <a href="{{ route('admin.transaksi-setor.index') }}"
               class="nav-item {{ request()->routeIs('admin.transaksi-setor.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-down"></i>
                <span>Setor Sampah</span>
            </a>
            <a href="{{ route('admin.transaksi-tarik.index') }}"
               class="nav-item {{ request()->routeIs('admin.transaksi-tarik.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-up"></i>
                <span>Tarik Saldo</span>
            </a>

            {{-- Laporan --}}
            @if(auth()->user()->isAdmin())
            <div class="nav-section">Laporan</div>
            <a href="{{ route('admin.laporan.index') }}"
               class="nav-item {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            @endif

        @elseif(auth()->user()->isNasabah())
            <a href="{{ route('nasabah.dashboard') }}"
               class="nav-item {{ request()->routeIs('nasabah.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        @endif
    </nav>

    {{-- User Info --}}
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="user-details">
                <p class="user-name">{{ auth()->user()->name }}</p>
                <p class="user-role">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="btn-logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
</aside>
