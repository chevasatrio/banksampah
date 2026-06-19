<?php

namespace Tests\Browser;

use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TransaksiSetorTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $petugas;
    private Nasabah $nasabah;
    private JenisSampah $jenisSampah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->create([
            'name'     => 'Petugas SIBANK',
            'email'    => 'petugas@sibank.com',
            'password' => bcrypt('password'),
            'role'     => 'petugas',
        ]);

        $this->nasabah = Nasabah::factory()->create([
            'nama'  => 'Dewi Sartika',
            'no_hp' => '081234567890',
            'saldo' => 0,
        ]);

        $kategori = KategoriSampah::factory()->create([
            'nama' => 'Anorganik',
        ]);

        $this->jenisSampah = JenisSampah::factory()->create([
            'nama'         => 'Botol Plastik',
            'harga_per_kg' => 1500,
            'kategori_id'  => $kategori->id,
            'is_active'    => true,
        ]);
    }

    /**
     * Petugas dapat membuka halaman form setor sampah.
     */
    public function test_petugas_dapat_melihat_halaman_form_setor()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->waitForText('Form Setor Sampah', 10)
                    ->assertSee('Form Setor Sampah')
                    ->assertPresent('select[name="nasabah_id"]')
                    ->assertPresent('select[name="items[0][jenis_sampah_id]"]')
                    ->assertPresent('input[name="items[0][berat_kg]"]');
        });
    }

    /**
     * State: Form Input -> Sukses (happy path satu item)
     * Petugas mencatat setor sampah dengan data valid, saldo nasabah bertambah.
     *
     * Controller redirect ke halaman show (detail transaksi), bukan index.
     * Flash message: "Transaksi berhasil dicatat. Total: Rp X.XXX"
     */
    public function test_petugas_dapat_mencatat_transaksi_setor_dengan_data_valid()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->waitFor('#nasabah_id', 10)
                    ->select('nasabah_id', (string) $this->nasabah->id)
                    ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                    ->type('items[0][berat_kg]', '4')
                    ->press('Simpan Transaksi')
                    ->waitForText('Transaksi berhasil dicatat', 10)
                    ->assertSee('Transaksi berhasil dicatat')
                    ->assertSee('Rp 6.000');
        });

        $this->assertEquals(6000.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Berat sampah 0 kg ditolak oleh validasi min:0.01.
     * Custom message: "Berat sampah minimal 0.01 KG."
     *
     * NOTE: HTML input punya min="0.01", jadi harus submit via JS
     * untuk bypass browser HTML5 validation.
     */
    public function test_setor_gagal_jika_berat_sampah_nol()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->waitFor('#nasabah_id', 10)
                    ->select('nasabah_id', (string) $this->nasabah->id)
                    ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id);

            // Set berat ke 0 via JS, lalu submit via JS (bypass HTML5 min validation)
            $browser->script([
                "document.querySelector('input[name=\"items[0][berat_kg]\"]').value = '0';",
                "document.getElementById('form-setor').submit();",
            ]);

            $browser->waitFor('.alert', 10)
                    ->assertSee('Berat sampah minimal 0.01 KG');
        });

        $this->assertEquals(0.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * BVA Min: berat 0.01 kg (batas bawah sesuai rule min:0.01) diterima sistem.
     */
    public function test_setor_berhasil_dengan_berat_batas_bawah()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->waitFor('#nasabah_id', 10)
                    ->select('nasabah_id', (string) $this->nasabah->id)
                    ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                    ->type('items[0][berat_kg]', '0.01')
                    ->press('Simpan Transaksi')
                    ->waitForText('Transaksi berhasil dicatat', 10)
                    ->assertSee('Transaksi berhasil dicatat');
        });

        $this->assertEquals(15.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * Berat 100 kg diterima sistem (tidak ada max rule).
     */
    public function test_setor_berhasil_dengan_berat_100_kg()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->waitFor('#nasabah_id', 10)
                    ->select('nasabah_id', (string) $this->nasabah->id)
                    ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                    ->type('items[0][berat_kg]', '100')
                    ->press('Simpan Transaksi')
                    ->waitForText('Transaksi berhasil dicatat', 10)
                    ->assertSee('Transaksi berhasil dicatat');
        });

        $this->assertEquals(150000.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Setor tanpa memilih jenis sampah ditolak sistem.
     * Custom message: "Jenis sampah wajib dipilih."
     *
     * NOTE: HTML select punya required, jadi harus submit via JS
     * untuk bypass browser HTML5 validation.
     */
    public function test_setor_gagal_jika_jenis_sampah_tidak_dipilih()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->waitFor('#nasabah_id', 10)
                    ->select('nasabah_id', (string) $this->nasabah->id);

            // Set berat via JS, jenis_sampah tetap kosong, submit via JS (bypass HTML5 required)
            $browser->script([
                "document.querySelector('input[name=\"items[0][berat_kg]\"]').value = '5';",
                "document.getElementById('form-setor').submit();",
            ]);

            $browser->waitFor('.alert', 10)
                    ->assertSee('Jenis sampah wajib dipilih');
        });
    }

    /**
     * Dua item valid: saldo bertambah sesuai akumulasi kedua item.
     * Button untuk tambah item: "Tambah Jenis Sampah"
     */
    public function test_setor_berhasil_dengan_dua_item_sampah_sekaligus()
    {
        $jenisB = JenisSampah::factory()->create([
            'nama'         => 'Kertas Koran',
            'harga_per_kg' => 1000,
            'kategori_id'  => $this->jenisSampah->kategori_id,
            'is_active'    => true,
        ]);

        $this->browse(function (Browser $browser) use ($jenisB) {
            $browser->loginAs($this->petugas)
                    ->visit('/admin/transaksi-setor/create')
                    ->waitFor('#nasabah_id', 10)
                    ->select('nasabah_id', (string) $this->nasabah->id)
                    ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                    ->type('items[0][berat_kg]', '5')
                    ->press('Tambah Jenis Sampah')
                    ->pause(500)
                    ->select('items[1][jenis_sampah_id]', (string) $jenisB->id)
                    ->type('items[1][berat_kg]', '2')
                    ->press('Simpan Transaksi')
                    ->waitForText('Transaksi berhasil dicatat', 10)
                    ->assertSee('Transaksi berhasil dicatat');
        });

        // (5 x 1500) + (2 x 1000) = 7500 + 2000 = 9500
        $this->assertEquals(9500.0, $this->nasabah->fresh()->saldo);
    }
}