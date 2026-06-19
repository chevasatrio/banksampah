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
     */
    public function test_admin_dapat_melihat_halaman_daftar_nasabah()
    {
        Nasabah::factory()->count(3)->sequence(
            ['no_hp' => '081200000001'],
            ['no_hp' => '081200000002'],
            ['no_hp' => '081200000003']
        )->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah')
                ->waitForText('Daftar Nasabah', 10)
                ->assertSee('Daftar Nasabah')
                ->assertPresent('table')
                ->assertPresent('.nasabah-row');
        });
    }

    /**
     * State: Form Input -> Sukses
     * Admin dapat mendaftarkan nasabah baru melalui form dengan data valid.
     */
    public function test_admin_dapat_menambah_nasabah_baru_dengan_data_valid()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->waitForText('Tambah Nasabah', 10)
                ->assertSee('Tambah Nasabah')
                ->type('nama', 'Siti Rahayu')
                ->type('nik', '3578015005800001')
                ->type('alamat', 'Jl. Kenanga No. 12, Surabaya')
                ->type('no_hp', '081298765432')
                // Button text di create.blade.php: "Simpan"
                ->press('Simpan')
                ->waitForText('berhasil', 10)
                ->assertPathIs('/admin/nasabah')
                // Flash message dari controller: "Nasabah berhasil ditambahkan."
                ->assertSee('Nasabah berhasil ditambahkan')
                ->assertSee('Siti Rahayu');
        });

        $this->assertDatabaseHas('nasabahs', [
            'nik' => '3578015005800001',
        ]);
    }

    /**
     * EP Invalid: NIK kurang dari 16 digit ditolak sistem.
     * Custom message: "NIK harus terdiri dari 16 digit."
     */
    public function test_form_nasabah_menolak_nik_kurang_dari_16_digit()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->waitFor('#nama', 10)
                ->type('nama', 'Budi Santoso')
                ->type('nik', '357801123456789') // 15 digit
                ->type('alamat', 'Jl. Mawar No. 5, Surabaya')
                ->type('no_hp', '081234567890')
                ->press('Simpan')
                ->waitFor('.alert', 10)
                // Custom validation message from NasabahRequest
                ->assertSee('NIK harus terdiri dari 16 digit');
        });

        $this->assertDatabaseMissing('nasabahs', [
            'nik' => '357801123456789',
        ]);
    }

    /**
     * EP Invalid: NIK duplikat ditolak sistem.
     * Custom message: "NIK sudah terdaftar."
     */
    public function test_form_nasabah_menolak_nik_duplikat()
    {
        Nasabah::factory()->create(['nik' => '3578011234567890', 'no_hp' => '081200000010']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah/create')
                ->waitFor('#nama', 10)
                ->type('nama', 'Nama Lain')
                ->type('nik', '3578011234567890')
                ->type('alamat', 'Alamat Lain No. 99')
                ->type('no_hp', '089999999999')
                ->press('Simpan')
                ->waitFor('.alert', 10)
                // Custom validation message from NasabahRequest
                ->assertSee('NIK sudah terdaftar');
        });
    }

    /**
     * Admin dapat mengubah data nasabah melalui form edit.
     * Button text di edit.blade.php: "Perbarui"
     * Flash message: "Data nasabah berhasil diperbarui."
     */
    public function test_admin_dapat_mengubah_data_nasabah()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Nama Lama',
            'no_hp' => '081200000020',
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->admin)
                ->visit("/admin/nasabah/{$nasabah->id}/edit")
                ->waitFor('#nama', 10)
                // Title di edit.blade.php: "Edit Data Nasabah"
                ->assertSee('Edit')
                ->type('nama', 'Nama Diubah')
                // Button text di edit.blade.php: "Perbarui"
                ->press('Perbarui')
                ->waitForText('berhasil', 10)
                ->assertPathIs('/admin/nasabah')
                ->assertSee('Data nasabah berhasil diperbarui')
                ->assertSee('Nama Diubah');
        });
    }

    /**
     * Fitur pencarian nasabah berdasarkan nama berfungsi dengan benar.
     * Search box submits via form GET (name="search").
     */
    public function test_fitur_pencarian_nasabah_berfungsi()
    {
        Nasabah::factory()->create(['nama' => 'Ahmad Yani', 'no_hp' => '081200000030']);
        Nasabah::factory()->create(['nama' => 'Budi Pekerti', 'no_hp' => '081200000031']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/admin/nasabah')
                ->waitFor('#search', 10)
                ->type('#search', 'Ahmad')
                // Submit search form by pressing Enter
                ->keys('#search', '{enter}')
                ->waitForText('Ahmad Yani', 10)
                ->assertSee('Ahmad Yani')
                ->assertDontSee('Budi Pekerti');
        });
    }

    /**
     * Admin dapat melihat detail/profil nasabah beserta saldo tabungannya.
     */
    public function test_admin_dapat_melihat_detail_nasabah()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Dewi Sartika',
            'no_hp' => '081200000040',
            'saldo' => 25000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->admin)
                ->visit("/admin/nasabah/{$nasabah->id}")
                ->waitForText('Dewi Sartika', 10)
                ->assertSee('Dewi Sartika')
                ->assertSee($nasabah->no_anggota)
                ->assertSee('Informasi Pribadi')
                ->assertSee('Informasi Tabungan')
                ->assertSee('Saldo Tabungan')
                ->assertSee('Rp 25.000');
        });
    }
}