<?php

use App\Models\User;
use App\Models\Nasabah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Manajemen Nasabah', function () {

    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    });

    // ──── Index (Daftar) ────

    it('admin dapat melihat daftar nasabah', function () {
        Nasabah::factory()->count(3)->sequence(
            ['no_hp' => '081200000070'],
            ['no_hp' => '081200000071'],
            ['no_hp' => '081200000072']
        )->create();

        $response = $this->get('/admin/nasabah');

        $response->assertStatus(200);
        $response->assertViewIs('admin.nasabah.index');
        $response->assertViewHas('nasabahs');
    });

    // ──── Store (Tambah) ────

    it('admin dapat mendaftarkan nasabah baru', function () {
        $data = [
            'nama' => 'Budi Santoso',
            'nik' => '3578011234567890',
            'alamat' => 'Jl. Mawar No. 5, Surabaya',
            'no_hp' => '081234567890',
        ];

        $response = $this->post('/admin/nasabah', $data);

        $response->assertRedirect(route('admin.nasabah.index'));
        $response->assertSessionHas('success', 'Nasabah berhasil ditambahkan.');
        $this->assertDatabaseHas('nasabahs', ['nik' => '3578011234567890']);
    });

    it('no_anggota dibuat otomatis saat nasabah ditambahkan', function () {
        $data = [
            'nama' => 'Siti Rahayu',
            'nik' => '3578015005800001',
            'alamat' => 'Jl. Kenanga No. 12',
            'no_hp' => '081298765432',
        ];

        $this->post('/admin/nasabah', $data);

        $nasabah = Nasabah::where('nik', '3578015005800001')->first();
        expect($nasabah->no_anggota)->toMatch('/^NSB-\d{5}$/');
    });

    // ──── Validasi ────

    it('validasi menolak NIK yang kurang dari 16 digit', function () {
        $response = $this->post('/admin/nasabah', [
            'nama' => 'Test User',
            'nik' => '357801123456789', // 15 digit
            'alamat' => 'Jl. Test',
            'no_hp' => '081234567890',
        ]);

        $response->assertSessionHasErrors('nik');
    });

    it('validasi menolak NIK yang duplikat', function () {
        Nasabah::factory()->create([
            'nik' => '3578011234567890',
            'no_hp' => '081200000073',
        ]);

        $response = $this->post('/admin/nasabah', [
            'nama' => 'Nama Lain',
            'nik' => '3578011234567890',
            'alamat' => 'Alamat Lain',
            'no_hp' => '089999999999',
        ]);

        $response->assertSessionHasErrors('nik');
    });

    it('validasi menolak tanpa nama', function () {
        $response = $this->post('/admin/nasabah', [
            'nama' => '',
            'nik' => '3578011234567890',
            'alamat' => 'Jl. Test',
            'no_hp' => '081234567890',
        ]);

        $response->assertSessionHasErrors('nama');
    });

    it('validasi menolak tanpa alamat', function () {
        $response = $this->post('/admin/nasabah', [
            'nama' => 'Test User',
            'nik' => '3578011234567890',
            'alamat' => '',
            'no_hp' => '081234567890',
        ]);

        $response->assertSessionHasErrors('alamat');
    });

    // ──── Update (Edit) ────

    it('admin dapat mengubah data nasabah', function () {
        $nasabah = Nasabah::factory()->create(['no_hp' => '081200000074']);

        $response = $this->put("/admin/nasabah/{$nasabah->id}", [
            'nama' => 'Nama Diubah',
            'nik' => $nasabah->nik,
            'alamat' => $nasabah->alamat,
            'no_hp' => $nasabah->no_hp,
        ]);

        $response->assertRedirect(route('admin.nasabah.index'));
        $response->assertSessionHas('success', 'Data nasabah berhasil diperbarui.');
        $this->assertDatabaseHas('nasabahs', ['nama' => 'Nama Diubah']);
    });

    it('update mengizinkan NIK yang sama milik nasabah sendiri', function () {
        $nasabah = Nasabah::factory()->create([
            'nik' => '3578011234567890',
            'no_hp' => '081200000075',
        ]);

        $response = $this->put("/admin/nasabah/{$nasabah->id}", [
            'nama' => 'Nama Baru',
            'nik' => '3578011234567890', // NIK sama (milik sendiri)
            'alamat' => 'Alamat Baru',
            'no_hp' => '081234567890',
        ]);

        $response->assertSessionDoesntHaveErrors('nik');
    });

    // ──── Show (Detail) ────

    it('admin dapat melihat detail nasabah', function () {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Dewi Sartika',
            'no_hp' => '081200000076',
        ]);

        $response = $this->get("/admin/nasabah/{$nasabah->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.nasabah.show');
        $response->assertSee('Dewi Sartika');
    });

    // ──── Pencarian ────

    it('fitur pencarian nasabah berfungsi berdasarkan nama', function () {
        Nasabah::factory()->create(['nama' => 'Ahmad Yani', 'no_hp' => '081200000077']);
        Nasabah::factory()->create(['nama' => 'Budi Pekerti', 'no_hp' => '081200000078']);

        $response = $this->get('/admin/nasabah?search=Ahmad');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Yani');
        $response->assertDontSee('Budi Pekerti');
    });

    // ──── Middleware ────

    it('guest tidak dapat mengakses halaman nasabah', function () {
        // Logout first
        auth()->logout();

        $response = $this->get('/admin/nasabah');

        $response->assertRedirect('/login');
    });
});
