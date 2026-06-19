<?php

use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use App\Models\TransaksiSetor;
use App\Models\User;
use App\Services\TransaksiSetorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('TransaksiSetorService', function () {

    beforeEach(function () {
        $this->service = new TransaksiSetorService();
    });

    // ──── hitungTotal() ────

    it('dapat menghitung total nilai setor dengan benar', function () {
        $items = [
            ['berat_kg' => 2.5, 'harga_per_kg' => 1000],
            ['berat_kg' => 1.0, 'harga_per_kg' => 2000],
        ];

        $total = $this->service->hitungTotal($items);

        expect($total)->toBe(4500.0);
    });

    it('mengembalikan nol untuk array kosong', function () {
        $total = $this->service->hitungTotal([]);

        expect($total)->toBe(0.0);
    });

    it('menghitung total dengan satu item', function () {
        $items = [
            ['berat_kg' => 3.0, 'harga_per_kg' => 1500],
        ];

        $total = $this->service->hitungTotal($items);

        expect($total)->toBe(4500.0);
    });

    it('menghitung total dengan desimal berat', function () {
        $items = [
            ['berat_kg' => 0.5, 'harga_per_kg' => 2000],
            ['berat_kg' => 1.5, 'harga_per_kg' => 1000],
        ];

        $total = $this->service->hitungTotal($items);

        // (0.5 × 2000) + (1.5 × 1000) = 1000 + 1500 = 2500
        expect($total)->toBe(2500.0);
    });

    // ──── generateKodeTransaksi() ────

    it('menghasilkan kode transaksi unik', function () {
        $kode1 = $this->service->generateKodeTransaksi();
        $kode2 = $this->service->generateKodeTransaksi();

        expect($kode1)->not->toBe($kode2);
    });

    it('menghasilkan kode transaksi dengan format STR-YYYYMMDD-XXXXX', function () {
        $kode = $this->service->generateKodeTransaksi();

        expect($kode)->toMatch('/^STR-\d{8}-[A-Z0-9]{5}$/');
    });

    it('menghasilkan kode transaksi dengan tanggal hari ini', function () {
        $kode = $this->service->generateKodeTransaksi();
        $today = now()->format('Ymd');

        expect($kode)->toContain("STR-{$today}-");
    });

    // ──── prosesSetor() ────

    it('menambah saldo nasabah setelah setor berhasil', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 5000, 'no_hp' => '081200000001']);
        $kategori = KategoriSampah::factory()->create();
        $jenisSampah = JenisSampah::factory()->create([
            'harga_per_kg' => 2000,
            'kategori_id' => $kategori->id,
        ]);

        $this->service->prosesSetor($nasabah, [
            ['jenis_sampah_id' => $jenisSampah->id, 'berat_kg' => 2],
        ], $petugas);

        // 5000 + (2 × 2000) = 9000
        expect($nasabah->fresh()->saldo)->toBe('9000.00');
    });

    it('membuat record transaksi_setors dan detail_setor_sampahs', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 0, 'no_hp' => '081200000002']);
        $kategori = KategoriSampah::factory()->create();
        $jenisSampah = JenisSampah::factory()->create([
            'harga_per_kg' => 1500,
            'kategori_id' => $kategori->id,
        ]);

        $transaksi = $this->service->prosesSetor($nasabah, [
            ['jenis_sampah_id' => $jenisSampah->id, 'berat_kg' => 4],
        ], $petugas);

        expect($transaksi)->toBeInstanceOf(TransaksiSetor::class);
        expect($transaksi->total_nilai)->toBe('6000.00');
        expect($transaksi->detailSetorSampahs)->toHaveCount(1);
        expect($transaksi->detailSetorSampahs->first()->berat_kg)->toBe('4.00');
    });

    it('mencatat harga_saat_itu sesuai harga jenis sampah saat transaksi', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 0, 'no_hp' => '081200000003']);
        $kategori = KategoriSampah::factory()->create();
        $jenisSampah = JenisSampah::factory()->create([
            'harga_per_kg' => 3000,
            'kategori_id' => $kategori->id,
        ]);

        $transaksi = $this->service->prosesSetor($nasabah, [
            ['jenis_sampah_id' => $jenisSampah->id, 'berat_kg' => 1],
        ], $petugas);

        $detail = $transaksi->detailSetorSampahs->first();
        expect($detail->harga_saat_itu)->toBe('3000.00');
        expect($detail->subtotal)->toBe('3000.00');
    });

    it('dapat memproses setor dengan multiple item', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 0, 'no_hp' => '081200000004']);
        $kategori = KategoriSampah::factory()->create();
        $jenisA = JenisSampah::factory()->create([
            'harga_per_kg' => 1500, 'kategori_id' => $kategori->id,
        ]);
        $jenisB = JenisSampah::factory()->create([
            'harga_per_kg' => 2000, 'kategori_id' => $kategori->id,
        ]);

        $transaksi = $this->service->prosesSetor($nasabah, [
            ['jenis_sampah_id' => $jenisA->id, 'berat_kg' => 5],
            ['jenis_sampah_id' => $jenisB->id, 'berat_kg' => 2],
        ], $petugas);

        // (5 × 1500) + (2 × 2000) = 7500 + 4000 = 11500
        expect($transaksi->total_nilai)->toBe('11500.00');
        expect($transaksi->detailSetorSampahs)->toHaveCount(2);
        expect($nasabah->fresh()->saldo)->toBe('11500.00');
    });
});
