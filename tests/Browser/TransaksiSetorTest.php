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
            'name' => 'Petugas SIBANK',
            'email' => 'petugas@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);

        $this->nasabah = Nasabah::factory()->create([
            'nama' => 'Dewi Sartika',
            'saldo' => 0,
        ]);

        $kategori = KategoriSampah::factory()->create([
            'nama' => 'Anorganik',
        ]);

        $this->jenisSampah = JenisSampah::factory()->create([
            'nama' => 'Botol Plastik',
            'harga_per_kg' => 1500,
            'kategori_id' => $kategori->id,
            'is_active' => true,
        ]);
    }

    /**
     * Petugas dapat membuka halaman form setor sampah.
     *
     * @test
     */
    public function petugas_dapat_melihat_halaman_form_setor()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->assertSee('Setor Sampah')
                ->assertPresent('select[name="nasabah_id"]')
                ->assertPresent('select[name="items[0][jenis_sampah_id]"]')
                ->assertPresent('input[name="items[0][berat_kg]"]');
        });
    }

    /**
     * State: Form Input -> Sukses (Path 4 - happy path satu item)
     * Petugas mencatat setor sampah dengan data valid, saldo nasabah bertambah,
     * dan sistem menampilkan notifikasi berhasil.
     *
     * @test
     */
    public function petugas_dapat_mencatat_transaksi_setor_dengan_data_valid()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->select('nasabah_id', (string) $this->nasabah->id)
                ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                ->type('items[0][berat_kg]', '4')
                ->press('Simpan Transaksi')
                ->waitForLocation('/admin/transaksi-setor')
                ->assertPathIs('/admin/transaksi-setor')
                ->assertSee('Transaksi berhasil dicatat')
                // 4 kg x Rp1.500 = Rp6.000
                ->assertSee('Rp 6.000');
        });

        $this->assertEquals(6000.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Berat sampah 0 kg ditolak sistem (BVA Min-1), saldo nasabah tidak berubah.
     *
     * @test
     */
    public function setor_gagal_jika_berat_sampah_nol()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->select('nasabah_id', (string) $this->nasabah->id)
                ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                ->type('items[0][berat_kg]', '0')
                ->press('Simpan Transaksi')
                ->assertPathIs('/admin/transaksi-setor/create')
                ->assertSee('Berat tidak valid');
        });

        $this->assertEquals(0.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * BVA Min: berat 0.1 kg (batas bawah) diterima sistem.
     *
     * @test
     */
    public function setor_berhasil_dengan_berat_batas_bawah_0_1_kg()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->select('nasabah_id', (string) $this->nasabah->id)
                ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                ->type('items[0][berat_kg]', '0.1')
                ->press('Simpan Transaksi')
                ->waitForLocation('/admin/transaksi-setor')
                ->assertPathIs('/admin/transaksi-setor')
                ->assertSee('Transaksi berhasil dicatat');
        });

        // 0.1 kg x Rp1.500 = Rp150
        $this->assertEquals(150.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * BVA Max: berat 100 kg (batas atas) diterima sistem.
     *
     * @test
     */
    public function setor_berhasil_dengan_berat_batas_atas_100_kg()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->select('nasabah_id', (string) $this->nasabah->id)
                ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                ->type('items[0][berat_kg]', '100')
                ->press('Simpan Transaksi')
                ->waitForLocation('/admin/transaksi-setor')
                ->assertSee('Transaksi berhasil dicatat');
        });

        // 100 kg x Rp1.500 = Rp150.000
        $this->assertEquals(150000.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * BVA Max+1: berat 100.1 kg ditolak karena melebihi batas maksimum.
     *
     * @test
     */
    public function setor_gagal_jika_berat_melebihi_batas_maksimum()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->select('nasabah_id', (string) $this->nasabah->id)
                ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                ->type('items[0][berat_kg]', '100.1')
                ->press('Simpan Transaksi')
                ->assertPathIs('/admin/transaksi-setor/create')
                ->assertSee('Berat tidak valid');
        });

        $this->assertEquals(0.0, $this->nasabah->fresh()->saldo);
    }

    /**
     * State tetap: Form Input
     * Setor tanpa memilih jenis sampah ditolak sistem.
     *
     * @test
     */
    public function setor_gagal_jika_jenis_sampah_tidak_dipilih()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->select('nasabah_id', (string) $this->nasabah->id)
                ->type('items[0][berat_kg]', '5')
                ->press('Simpan Transaksi')
                ->assertPathIs('/admin/transaksi-setor/create')
                ->assertSee('The items.0.jenis sampah id field is required');
        });
    }

    /**
     * Path 6 (Basis Path Testing): dua item valid (loop dua kali),
     * saldo bertambah sesuai akumulasi kedua item.
     *
     * @test
     */
    public function setor_berhasil_dengan_dua_item_sampah_sekaligus()
    {
        $jenisB = JenisSampah::factory()->create([
            'nama' => 'Kertas Koran',
            'harga_per_kg' => 1000,
            'kategori_id' => $this->jenisSampah->kategori_id,
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($jenisB) {
            $browser->loginAs($this->petugas)
                ->visit('/admin/transaksi-setor/create')
                ->select('nasabah_id', (string) $this->nasabah->id)
                ->select('items[0][jenis_sampah_id]', (string) $this->jenisSampah->id)
                ->type('items[0][berat_kg]', '5')
                ->press('Tambah Item') // tombol untuk menambahkan baris item sampah
                ->select('items[1][jenis_sampah_id]', (string) $jenisB->id)
                ->type('items[1][berat_kg]', '2')
                ->press('Simpan Transaksi')
                ->waitForLocation('/admin/transaksi-setor')
                ->assertSee('Transaksi berhasil dicatat');
        });

        // (5 x 1500) + (2 x 1000) = 7500 + 2000 = 9500
        $this->assertEquals(9500.0, $this->nasabah->fresh()->saldo);
    }
}