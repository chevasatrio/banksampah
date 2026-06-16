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
     */
    public function test_petugas_dapat_melihat_halaman_form_tarik_saldo()
    {
        // Create nasabah with saldo > 0, because the create page only shows nasabahs with saldo > 0
        Nasabah::factory()->create(['saldo' => 50000]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->waitForText('Form Penarikan Saldo', 10)
                ->assertSee('Form Penarikan Saldo')
                ->assertPresent('select[name="nasabah_id"]')
                ->assertPresent('input[name="jumlah"]');
        });
    }

    /**
     * State: Form Input -> Sukses
     * Penarikan dengan saldo cukup berhasil, saldo nasabah berkurang.
     *
     * Flash message: "Penarikan berhasil dicatat. Jumlah: Rp X.XXX"
     * Button text: "Proses Penarikan"
     */
    public function test_tarik_saldo_berhasil_jika_saldo_cukup()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Dewi Sartika',
            'saldo' => 50000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->waitFor('#nasabah_id', 10)
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '20000')
                ->type('keterangan', 'Penarikan tunai oleh nasabah')
                // Button text di create.blade.php: "Proses Penarikan"
                ->press('Proses Penarikan')
                ->waitForLocation('/admin/transaksi-tarik', 10)
                ->assertPathIs('/admin/transaksi-tarik')
                // Flash message from controller
                ->assertSee('Penarikan berhasil dicatat');
        });

        $this->assertEquals(30000.0, $nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Penarikan ditolak jika jumlah melebihi saldo yang tersedia.
     * Error message from TransaksiTarikService: "Saldo tidak mencukupi..."
     */
    public function test_tarik_saldo_ditolak_jika_saldo_tidak_cukup()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Ahmad Yani',
            'saldo' => 10000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->waitFor('#nasabah_id', 10)
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '20000')
                ->press('Proses Penarikan')
                ->waitFor('.alert', 10)
                // Validation message from TransaksiTarikService
                ->assertSee('Saldo tidak mencukupi');
        });

        $this->assertEquals(10000.0, $nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Penarikan dengan jumlah kosong ditolak sistem.
     * Custom message: "Jumlah penarikan wajib diisi."
     */
    public function test_tarik_saldo_ditolak_jika_jumlah_kosong()
    {
        $nasabah = Nasabah::factory()->create([
            'saldo' => 50000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->waitFor('#nasabah_id', 10)
                ->select('nasabah_id', (string) $nasabah->id)
                ->press('Proses Penarikan')
                ->waitFor('.alert', 10)
                // Custom validation message from TransaksiTarikRequest
                ->assertSee('Jumlah penarikan wajib diisi');
        });

        $this->assertEquals(50000.0, $nasabah->fresh()->saldo);
    }

    /**
     * Penarikan tepat sebesar saldo yang tersedia (boundary: saldo == jumlah)
     * tetap diterima sistem dan menyisakan saldo 0.
     */
    public function test_tarik_saldo_berhasil_jika_jumlah_sama_dengan_saldo()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Budi Pekerti',
            'saldo' => 15000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->waitFor('#nasabah_id', 10)
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '15000')
                ->press('Proses Penarikan')
                ->waitForLocation('/admin/transaksi-tarik', 10)
                ->assertSee('Penarikan berhasil dicatat');
        });

        $this->assertEquals(0.0, $nasabah->fresh()->saldo);
    }

    /**
     * Petugas dapat melihat riwayat transaksi tarik pada halaman daftar.
     */
    public function test_petugas_dapat_melihat_riwayat_transaksi_tarik()
    {
        $nasabah = Nasabah::factory()->create([
            'nama' => 'Citra Lestari',
            'saldo' => 100000,
        ]);

        $this->browse(function (Browser $browser) use ($nasabah) {
            // Lakukan satu kali penarikan terlebih dahulu
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-tarik/create')
                ->waitFor('#nasabah_id', 10)
                ->select('nasabah_id', (string) $nasabah->id)
                ->type('jumlah', '25000')
                ->press('Proses Penarikan')
                ->waitForLocation('/admin/transaksi-tarik', 10);

            // Cek riwayat transaksi muncul di halaman daftar
            $browser->visit('/admin/transaksi-tarik')
                ->waitForText('Citra Lestari', 10)
                ->assertSee('Citra Lestari')
                ->assertSee('25.000');
        });
    }
}