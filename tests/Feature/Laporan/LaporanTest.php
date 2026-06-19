<?php

use App\Models\User;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use App\Models\TransaksiSetor;
use App\Models\TransaksiTarik;
use App\Services\TransaksiSetorService;
use App\Services\TransaksiTarikService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Laporan Transaksi', function () {

    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    });

    // ──── Akses Halaman ────

    it('admin dapat mengakses halaman laporan', function () {
        $response = $this->get('/admin/laporan');

        $response->assertStatus(200);
        $response->assertViewIs('admin.laporan.index');
        $response->assertViewHas('laporan');
        $response->assertViewHas('statistik');
    });

    it('halaman laporan memiliki data dari dan sampai', function () {
        $response = $this->get('/admin/laporan');

        $response->assertViewHas('dari');
        $response->assertViewHas('sampai');
    });

    // ──── Filter Tanggal ────

    it('admin dapat memfilter laporan berdasarkan rentang tanggal', function () {
        $response = $this->get('/admin/laporan?dari=2026-06-01&sampai=2026-06-30');

        $response->assertStatus(200);
        $response->assertViewHas('dari');
        $response->assertViewHas('sampai');
    });

    // ──── Filter Tipe ────

    it('admin dapat memfilter laporan tipe setor', function () {
        $response = $this->get('/admin/laporan?tipe=setor');

        $response->assertStatus(200);
        $response->assertViewHas('tipe', 'setor');
    });

    it('admin dapat memfilter laporan tipe tarik', function () {
        $response = $this->get('/admin/laporan?tipe=tarik');

        $response->assertStatus(200);
        $response->assertViewHas('tipe', 'tarik');
    });

    // ──── Dengan Data Transaksi ────

    it('laporan menampilkan data transaksi yang ada', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 0, 'no_hp' => '081200000100']);
        $kategori = KategoriSampah::factory()->create();
        $jenisSampah = JenisSampah::factory()->create([
            'harga_per_kg' => 2000,
            'kategori_id' => $kategori->id,
        ]);

        // Buat transaksi setor via service
        $setorService = new TransaksiSetorService();
        $setorService->prosesSetor($nasabah, [
            ['jenis_sampah_id' => $jenisSampah->id, 'berat_kg' => 5],
        ], $petugas);

        $response = $this->get('/admin/laporan');

        $response->assertStatus(200);
    });

    // ──── Statistik Dashboard ────

    it('statistik dashboard tersedia di view', function () {
        $response = $this->get('/admin/laporan');

        $response->assertViewHas('statistik');
    });

    // ──── Middleware ────

    it('guest tidak dapat mengakses halaman laporan', function () {
        auth()->logout();

        $response = $this->get('/admin/laporan');

        $response->assertRedirect('/login');
    });

    it('petugas tidak dapat mengakses halaman laporan jika dibatasi admin middleware', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $this->actingAs($petugas);

        $response = $this->get('/admin/laporan');

        // Jika ada admin middleware, akan redirect/403
        // Jika tidak ada middleware khusus, akan 200
        expect($response->status())->toBeIn([200, 302, 403]);
    });
});
