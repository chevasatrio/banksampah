<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Autentikasi', function () {

    // ──── Halaman Login ────

    it('menampilkan halaman login', function () {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('SIBANK');
        $response->assertSee('Masuk');
    });

    // ──── Login Berhasil ────

    it('admin dapat login dengan kredensial yang benar', function () {
        $user = User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@sibank.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    });

    it('petugas diarahkan ke halaman nasabah setelah login', function () {
        $user = User::factory()->create([
            'email' => 'petugas@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);

        $response = $this->post('/login', [
            'email' => 'petugas@sibank.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/nasabah');
        $this->assertAuthenticatedAs($user);
    });

    // ──── Login Gagal ────

    it('menolak login dengan password salah', function () {
        User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@sibank.com',
            'password' => 'salah123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('menolak login dengan email tidak terdaftar', function () {
        $response = $this->post('/login', [
            'email' => 'tidakada@sibank.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('menampilkan pesan error spesifik saat login gagal', function () {
        User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@sibank.com',
            'password' => 'salah',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email atau password salah.',
        ]);
    });

    // ──── Validasi Input ────

    it('menolak login tanpa email', function () {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    });

    it('menolak login tanpa password', function () {
        $response = $this->post('/login', [
            'email' => 'admin@sibank.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    });

    // ──── Middleware ────

    it('pengguna tidak dapat mengakses dashboard tanpa login', function () {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    });

    // ──── Logout ────

    it('pengguna dapat logout', function () {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    });

    it('menampilkan pesan sukses setelah logout', function () {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertSessionHas('success', 'Anda telah berhasil logout.');
    });
});
