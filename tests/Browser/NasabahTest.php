<?php

namespace Tests\Browser;

use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NasabahTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin SIBANK',
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /**
     * Admin dapat membuka halaman daftar nasabah.
     *
     * @test
     */
    public function admin_dapat_melihat_halaman_daftar_nasabah()
    {
        Nasabah::factory()->count(3)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah')
                ->assertSee('Daftar Nasabah')
                ->assertPresent('table')
                ->assertPresent('.nasabah-row');
        });
    }

    /**
     * State: Form Input -> Sukses
     * Admin dapat mendaftarkan nasabah baru melalui form dengan data valid.
     *
     * @test
     */
    public function admin_dapat_menambah_nasabah_baru_dengan_data_valid()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->assertSee('Tambah Nasabah')
                ->type('nama', 'Siti Rahayu')
                ->type('nik', '3578015005800001')
                ->type('alamat', 'Jl. Kenanga No. 12, Surabaya')
                ->type('no_hp', '081298765432')
                ->press('Simpan')
                ->waitForLocation('/admin/nasabah')
                ->assertPathIs('/admin/nasabah')
                ->assertSee('Nasabah berhasil ditambahkan')
                ->assertSee('Siti Rahayu');
        });

        $this->assertDatabaseHas('nasabahs', [
            'nik' => '3578015005800001',
        ]);
    }

    /**
     * EP Invalid: NIK kurang dari 16 digit ditolak sistem,
     * State tetap di Form Input dengan pesan error.
     *
     * @test
     */
    public function form_nasabah_menolak_nik_kurang_dari_16_digit()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->type('nama', 'Budi Santoso')
                ->type('nik', '357801123456789') // 15 digit
                ->type('alamat', 'Jl. Mawar No. 5, Surabaya')
                ->type('no_hp', '081234567890')
                ->press('Simpan')
                ->assertPathIs('/admin/nasabah/create')
                ->assertSee('The nik field must be 16 characters');
        });

        $this->assertDatabaseMissing('nasabahs', [
            'nik' => '357801123456789',
        ]);
    }

    /**
     * EP Invalid: NIK mengandung huruf ditolak sistem.
     *
     * @test
     */
    public function form_nasabah_menolak_nik_mengandung_huruf()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->type('nama', 'Budi Santoso')
                ->type('nik', '35780A1234567890')
                ->type('alamat', 'Jl. Mawar No. 5, Surabaya')
                ->type('no_hp', '081234567890')
                ->press('Simpan')
                ->assertPathIs('/admin/nasabah/create')
                ->assertSee('The nik field must be numeric');
        });
    }

    /**
     * EP Invalid: NIK duplikat ditolak sistem.
     *
     * @test
     */
    public function form_nasabah_menolak_nik_duplikat()
    {
        Nasabah::factory()->create(['nik' => '3578011234567890']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->type('nama', 'Nama Lain')
                ->type('nik', '3578011234567890')
                ->type('alamat', 'Alamat Lain No. 99')
                ->type('no_hp', '089999999999')
                ->press('Simpan')
                ->assertPathIs('/admin/nasabah/create')
                ->assertSee('The nik has already been taken');
        });
    }

    /**
     * EP Invalid: No HP tidak diawali angka 0 ditolak sistem.
     *
     * @test
     */
    public function form_nasabah_menolak_no_hp_tidak_diawali_nol()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->type('nama', 'Budi Santoso')
                ->type('nik', '3578011234567891')
                ->type('alamat', 'Jl. Mawar No. 5, Surabaya')
                ->type('no_hp', '181234567890')
                ->press('Simpan')
                ->assertPathIs('/admin/nasabah/create')
                ->assertSee('The no hp format is invalid');
        });
    }

    /**
     * Admin dapat mengubah data nasabah melalui form edit.
     *
     * @test
     */
    public function admin_dapat_mengubah_data_nasabah()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Nama Lama',
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->admin)
                ->visit("/admin/nasabah/{$nasabah->id}/edit")
                ->assertSee('Edit Nasabah')
                ->type('nama', 'Nama Diubah')
                ->press('Simpan')
                ->waitForLocation('/admin/nasabah')
                ->assertPathIs('/admin/nasabah')
                ->assertSee('Data nasabah berhasil diperbarui')
                ->assertSee('Nama Diubah')
                ->assertDontSee('Nama Lama');
        });
    }

    /**
     * Fitur pencarian nasabah berdasarkan nama berfungsi dengan benar.
     *
     * @test
     */
    public function fitur_pencarian_nasabah_berfungsi()
    {
        Nasabah::factory()->create(['nama' => 'Ahmad Yani']);
        Nasabah::factory()->create(['nama' => 'Budi Pekerti']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah')
                ->type('#search', 'Ahmad')
                ->waitFor('.nasabah-row')
                ->assertSee('Ahmad Yani')
                ->assertDontSee('Budi Pekerti');
        });
    }

    /**
     * Admin dapat melihat detail/profil nasabah beserta saldo tabungannya.
     *
     * @test
     */
    public function admin_dapat_melihat_detail_nasabah()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Dewi Sartika',
            'saldo' => 25000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->admin)
                ->visit("/admin/nasabah/{$nasabah->id}")
                ->assertSee('Dewi Sartika')
                ->assertSee($nasabah->no_anggota)
                ->assertSee('Rp 25.000');
        });
    }
}