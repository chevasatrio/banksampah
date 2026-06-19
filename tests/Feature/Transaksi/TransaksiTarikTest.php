<?php

use App\Models\User;
use App\Models\Nasabah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Transaksi Tarik Saldo', function () {

    beforeEach(function () {
        $this->petugas = User::factory()->create(['role' => 'petugas']);
        $this->actingAs($this->petugas);
    });

    // ──── Halaman Form ────

    it('petugas dapat mengakses halaman form tarik', function () {
        Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000090']);

        $response = $this->get('/admin/transaksi-tarik/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.transaksi-tarik.create');
        $response->assertViewHas('nasabahs');
    });

    // ──── Tarik Berhasil ────

    it('petugas dapat mencatat penarikan saldo', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000091']);

        $response = $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => $nasabah->id,
            'jumlah' => 20000,
            'keterangan' => 'Penarikan tunai',
        ]);

        $response->assertRedirect(route('admin.transaksi-tarik.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transaksi_tariks', [
            'nasabah_id' => $nasabah->id,
            'jumlah' => 20000,
        ]);
    });

    it('saldo nasabah berkurang setelah tarik', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000092']);

        $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => $nasabah->id,
            'jumlah' => 20000,
        ]);

        // 50000 - 20000 = 30000
        expect($nasabah->fresh()->saldo)->toBe('30000.00');
    });

    it('flash message menampilkan jumlah penarikan', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000093']);

        $response = $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => $nasabah->id,
            'jumlah' => 15000,
        ]);

        $successMessage = session('success');
        expect($successMessage)->toContain('Penarikan berhasil dicatat');
    });

    it('tarik tepat sebesar saldo berhasil (boundary)', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 25000, 'no_hp' => '081200000094']);

        $response = $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => $nasabah->id,
            'jumlah' => 25000,
        ]);

        $response->assertRedirect(route('admin.transaksi-tarik.index'));
        expect($nasabah->fresh()->saldo)->toBe('0.00');
    });

    // ──── Tarik Gagal ────

    it('menolak penarikan jika saldo tidak mencukupi', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 10000, 'no_hp' => '081200000095']);

        $response = $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => $nasabah->id,
            'jumlah' => 20000,
        ]);

        $response->assertSessionHasErrors('jumlah');
        expect($nasabah->fresh()->saldo)->toBe('10000.00');
    });

    // ──── Validasi ────

    it('menolak penarikan tanpa nasabah_id', function () {
        $response = $this->post('/admin/transaksi-tarik', [
            'jumlah' => 10000,
        ]);

        $response->assertSessionHasErrors('nasabah_id');
    });

    it('menolak penarikan tanpa jumlah', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000096']);

        $response = $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => $nasabah->id,
        ]);

        $response->assertSessionHasErrors('jumlah');
    });

    it('menolak penarikan dengan jumlah kurang dari 1', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000097']);

        $response = $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => $nasabah->id,
            'jumlah' => 0,
        ]);

        $response->assertSessionHasErrors('jumlah');
    });

    it('menolak penarikan dengan nasabah_id tidak valid', function () {
        $response = $this->post('/admin/transaksi-tarik', [
            'nasabah_id' => 99999,
            'jumlah' => 10000,
        ]);

        $response->assertSessionHasErrors('nasabah_id');
    });

    // ──── Daftar Transaksi ────

    it('petugas dapat melihat daftar transaksi tarik', function () {
        $response = $this->get('/admin/transaksi-tarik');

        $response->assertStatus(200);
        $response->assertViewIs('admin.transaksi-tarik.index');
    });

    // ──── Middleware ────

    it('guest tidak dapat mengakses form tarik', function () {
        auth()->logout();

        $response = $this->get('/admin/transaksi-tarik/create');

        $response->assertRedirect('/login');
    });
});
