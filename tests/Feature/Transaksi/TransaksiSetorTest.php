<?php

use App\Models\User;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Transaksi Setor Sampah', function () {

    beforeEach(function () {
        $this->petugas = User::factory()->create(['role' => 'petugas']);
        $this->nasabah = Nasabah::factory()->create(['saldo' => 0, 'no_hp' => '081200000080']);
        $this->kategori = KategoriSampah::factory()->create(['nama' => 'Anorganik']);
        $this->jenisSampah = JenisSampah::factory()->create([
            'nama' => 'Plastik PET',
            'harga_per_kg' => 2000,
            'kategori_id' => $this->kategori->id,
            'is_active' => true,
        ]);
        $this->actingAs($this->petugas);
    });

    // ──── Halaman Form ────

    it('petugas dapat mengakses halaman form setor', function () {
        $response = $this->get('/admin/transaksi-setor/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.transaksi-setor.create');
        $response->assertViewHas('nasabahs');
        $response->assertViewHas('jenisSampahs');
    });

    // ──── Setor Berhasil ────

    it('petugas dapat mencatat setor sampah', function () {
        $response = $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
            'items' => [
                [
                    'jenis_sampah_id' => $this->jenisSampah->id,
                    'berat_kg' => 3,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi_setors', [
            'nasabah_id' => $this->nasabah->id,
        ]);
    });

    it('saldo nasabah bertambah setelah setor', function () {
        $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
            'items' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat_kg' => 5],
            ],
        ]);

        // 5 kg × Rp2.000 = Rp10.000
        expect($this->nasabah->fresh()->saldo)->toBe('10000.00');
    });

    it('flash message menampilkan total nilai setor', function () {
        $response = $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
            'items' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat_kg' => 4],
            ],
        ]);

        // 4 × 2000 = 8000
        $response->assertSessionHas('success');
        $successMessage = session('success');
        expect($successMessage)->toContain('Transaksi berhasil dicatat');
    });

    it('dapat mencatat setor dengan multiple item', function () {
        $jenisB = JenisSampah::factory()->create([
            'nama' => 'Kertas Koran',
            'harga_per_kg' => 1000,
            'kategori_id' => $this->kategori->id,
            'is_active' => true,
        ]);

        $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
            'items' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat_kg' => 5],
                ['jenis_sampah_id' => $jenisB->id, 'berat_kg' => 2],
            ],
        ]);

        // (5 × 2000) + (2 × 1000) = 10000 + 2000 = 12000
        expect($this->nasabah->fresh()->saldo)->toBe('12000.00');
    });

    // ──── Validasi ────

    it('menolak setor tanpa nasabah_id', function () {
        $response = $this->post('/admin/transaksi-setor', [
            'items' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat_kg' => 3],
            ],
        ]);

        $response->assertSessionHasErrors('nasabah_id');
    });

    it('menolak setor tanpa items', function () {
        $response = $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
        ]);

        $response->assertSessionHasErrors('items');
    });

    it('menolak setor dengan berat kurang dari 0.01', function () {
        $response = $this->post('/admin/transaksi-setor', [
            'nasabah_id' => $this->nasabah->id,
            'items' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat_kg' => 0],
            ],
        ]);

        $response->assertSessionHasErrors();
        expect($this->nasabah->fresh()->saldo)->toBe('0.00');
    });

    // ──── Daftar Transaksi ────

    it('petugas dapat melihat daftar transaksi setor', function () {
        $response = $this->get('/admin/transaksi-setor');

        $response->assertStatus(200);
        $response->assertViewIs('admin.transaksi-setor.index');
    });

    // ──── Middleware ────

    it('guest tidak dapat mengakses form setor', function () {
        auth()->logout();

        $response = $this->get('/admin/transaksi-setor/create');

        $response->assertRedirect('/login');
    });
});
