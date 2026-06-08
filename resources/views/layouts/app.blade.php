<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIBANK') — Sistem Informasi Bank Sampah</title>
    <meta name="description" content="SIBANK - Sistem Informasi Bank Sampah Digital untuk pengelolaan sampah berbasis tabungan masyarakat">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/sibank.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Main Content --}}
        <div class="main-content">
            {{-- Navbar --}}
            @include('layouts.navbar')

            {{-- Flash Messages --}}
            <div class="content-area">
                @if(session('success'))
                    <div class="alert alert-success" id="flash-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                        <button class="alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error" id="flash-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button class="alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Terdapat kesalahan:</strong>
                            <ul style="margin:4px 0 0 16px; list-style:disc;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Page Content --}}
                @yield('content')
            </div>
        </div>
    </div>

    {{-- Sidebar Toggle Script --}}
    <script>
        // Auto-dismiss flash messages
        setTimeout(() => {
            const flash = document.getElementById('flash-success');
            if (flash) flash.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => { if (flash) flash.remove(); }, 300);
        }, 4000);

        // Sidebar toggle for mobile
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('sidebar-open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }

        // Close sidebar on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('sidebar-overlay')) {
                toggleSidebar();
            }
        });

        // Format Rupiah
        function formatRupiah(angka) {
            return 'Rp ' + Number(angka).toLocaleString('id-ID');
        }
    </script>
    @stack('scripts')
</body>
</html>
