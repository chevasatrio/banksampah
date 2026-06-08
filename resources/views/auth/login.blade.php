<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIBANK</title>
    <meta name="description" content="Masuk ke Sistem Informasi Bank Sampah">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/sibank.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-brand">
                <div class="brand-icon">
                    <i class="fas fa-recycle"></i>
                </div>
                <h1>SIBANK</h1>
                <p>Sistem Informasi Bank Sampah</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="login-form">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="Masukkan email anda" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="Masukkan password" required>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="remember" name="remember" style="accent-color: var(--primary);">
                    <label for="remember" style="margin: 0; font-weight: 400; font-size: 13px; color: var(--text-secondary);">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk
                </button>
            </form>

            <p style="text-align: center; margin-top: 24px; font-size: 12px; color: var(--text-muted);">
                &copy; {{ date('Y') }} SIBANK — Bank Sampah Digital
            </p>
        </div>
    </div>
</body>
</html>
