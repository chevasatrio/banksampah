<?php

namespace Tests\Browser;

use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TransaksiTarikTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->create([
            'name' => 'Petugas SIBANK',
            'email' => 'petugas@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);
    }

    /**
     * Petugas dapat membuka halaman form tarik saldo.
     *
     * @test
     */
    public function petugas_dapat_melihat_halaman_form_tarik_saldo()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->assertSee('Tarik Saldo')
                ->assertPresent('select[name="nasabah_id"]')
                ->assertPresent('input[name="jumlah"]');
        });
    }

    /**
     * State: Form Input -> Sukses
     * Penarikan dengan saldo cukup berhasil, saldo nasabah berkurang
     * sesuai jumlah penarikan.
     *
     * @test
     */
    public function tarik_saldo_berhasil_jika_saldo_cukup()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Dewi Sartika',
            'saldo' => 50000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '20000')
                ->type('keterangan', 'Penarikan tunai oleh nasabah')
                ->press('Simpan')
                ->waitForLocation('/admin/transaksi-tarik')
                ->assertPathIs('/admin/transaksi-tarik')
                ->assertSee('Penarikan berhasil dicatat')
                ->assertSee('Rp 20.000');
        });

        $this->assertEquals(30000.0, $nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Penarikan ditolak jika jumlah melebihi saldo yang tersedia,
     * saldo nasabah tidak berubah.
     *
     * @test
     */
    public function tarik_saldo_ditolak_jika_saldo_tidak_cukup()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Ahmad Yani',
            'saldo' => 10000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '20000')
                ->press('Simpan')
                ->assertPathIs('/admin/transaksi-tarik/create')
                ->assertSee('Saldo tidak cukup untuk melakukan penarikan');
        });

        $this->assertEquals(10000.0, $nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Penarikan dengan jumlah 0 atau kosong ditolak sistem.
     *
     * @test
     */
    public function tarik_saldo_ditolak_jika_jumlah_kosong()
    {
        $nasabah = Nasabah::factory()->create([
            'saldo' => 50000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->select('nasabah_id', (string) $nasabah->id)
                ->press('Simpan')
                ->assertPathIs('/admin/transaksi-tarik/create')
                ->assertSee('The jumlah field is required');
        });

        $this->assertEquals(50000.0, $nasabah->fresh()->saldo);
    }

    /**
     * Penarikan tepat sebesar saldo yang tersedia (boundary: saldo == jumlah)
     * tetap diterima sistem dan menyisakan saldo 0.
     *
     * @test
     */
    public function tarik_saldo_berhasil_jika_jumlah_sama_dengan_saldo()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Budi Pekerti',
            'saldo' => 15000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '15000')
                ->press('Simpan')
                ->waitForLocation('/admin/transaksi-tarik')
                ->assertSee('Penarikan berhasil dicatat');
        });

        $this->assertEquals(0.0, $nasabah->fresh()->saldo);
    }

    /**
     * Admin/Petugas dapat melihat riwayat transaksi tarik pada halaman daftar.
     *
     * @test
     */
    public function petugas_dapat_melihat_riwayat_transaksi_tarik()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Citra Lestari',
            'saldo' => 100000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            // Lakukan satu kali penarikan terlebih dahulu
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '25000')
                ->press('Simpan')
                ->waitForLocation('/admin/transaksi-tarik');

            // Cek riwayat transaksi muncul di halaman daftar
            $browser->visit('/admin/transaksi-tarik')
                ->assertSee('Citra Lestari')
                ->assertSee('Rp 25.000');
        });
    }
}