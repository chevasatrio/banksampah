<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NasabahController;
use App\Http\Controllers\Admin\KategoriSampahController;
use App\Http\Controllers\Admin\JenisSampahController;
use App\Http\Controllers\Admin\TransaksiSetorController;
use App\Http\Controllers\Admin\TransaksiTarikController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Nasabah\DashboardController as NasabahDashboardController;
use Illuminate\Support\Facades\Route;

// ──── Guest Routes ────

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ──── Authenticated Routes ────

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Redirect root to appropriate dashboard
    Route::get('/', function () {
        $user = auth()->user();
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('admin.nasabah.index'),
            'nasabah' => redirect()->route('nasabah.dashboard'),
            default => redirect()->route('login'),
        };
    });

    // ──── Admin & Petugas Routes ────
    Route::prefix('admin')->middleware('petugas')->group(function () {
        // Dashboard (admin only)
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard')
            ->middleware('admin');

        // Nasabah Management
        Route::resource('nasabah', NasabahController::class)
            ->names('admin.nasabah');
        Route::patch('/nasabah/{nasabah}/toggle-active', [NasabahController::class, 'toggleActive'])
            ->name('admin.nasabah.toggle-active');

        // Kategori Sampah (admin only)
        Route::middleware('admin')->group(function () {
            Route::get('/kategori-sampah', [KategoriSampahController::class, 'index'])
                ->name('admin.kategori-sampah.index');
            Route::post('/kategori-sampah', [KategoriSampahController::class, 'store'])
                ->name('admin.kategori-sampah.store');
            Route::put('/kategori-sampah/{kategoriSampah}', [KategoriSampahController::class, 'update'])
                ->name('admin.kategori-sampah.update');
            Route::delete('/kategori-sampah/{kategoriSampah}', [KategoriSampahController::class, 'destroy'])
                ->name('admin.kategori-sampah.destroy');
        });

        // Jenis Sampah (admin only for create/update, all petugas can view)
        Route::get('/jenis-sampah', [JenisSampahController::class, 'index'])
            ->name('admin.jenis-sampah.index');
        Route::middleware('admin')->group(function () {
            Route::post('/jenis-sampah', [JenisSampahController::class, 'store'])
                ->name('admin.jenis-sampah.store');
            Route::put('/jenis-sampah/{jenisSampah}', [JenisSampahController::class, 'update'])
                ->name('admin.jenis-sampah.update');
            Route::patch('/jenis-sampah/{jenisSampah}/toggle-active', [JenisSampahController::class, 'toggleActive'])
                ->name('admin.jenis-sampah.toggle-active');
        });

        // Transaksi Setor
        Route::get('/transaksi-setor', [TransaksiSetorController::class, 'index'])
            ->name('admin.transaksi-setor.index');
        Route::get('/transaksi-setor/create', [TransaksiSetorController::class, 'create'])
            ->name('admin.transaksi-setor.create');
        Route::post('/transaksi-setor', [TransaksiSetorController::class, 'store'])
            ->name('admin.transaksi-setor.store');
        Route::get('/transaksi-setor/{transaksiSetor}', [TransaksiSetorController::class, 'show'])
            ->name('admin.transaksi-setor.show');

        // Transaksi Tarik
        Route::get('/transaksi-tarik', [TransaksiTarikController::class, 'index'])
            ->name('admin.transaksi-tarik.index');
        Route::get('/transaksi-tarik/create', [TransaksiTarikController::class, 'create'])
            ->name('admin.transaksi-tarik.create');
        Route::post('/transaksi-tarik', [TransaksiTarikController::class, 'store'])
            ->name('admin.transaksi-tarik.store');

        // Laporan (admin only)
        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('admin.laporan.index')
            ->middleware('admin');
    });

    // ──── Nasabah Routes ────
    Route::prefix('nasabah')->group(function () {
        Route::get('/dashboard', [NasabahDashboardController::class, 'index'])
            ->name('nasabah.dashboard');
    });
});
