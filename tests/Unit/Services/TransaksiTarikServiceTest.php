<?php

use App\Models\Nasabah;
use App\Models\TransaksiTarik;
use App\Models\User;
use App\Services\TransaksiTarikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('TransaksiTarikService', function () {

    beforeEach(function () {
        $this->service = new TransaksiTarikService();
    });

    // ──── generateKodeTransaksi() ────

    it('menghasilkan kode transaksi unik', function () {
        $kode1 = $this->service->generateKodeTransaksi();
        $kode2 = $this->service->generateKodeTransaksi();

        expect($kode1)->not->toBe($kode2);
    });

    it('menghasilkan kode transaksi dengan format TRK-YYYYMMDD-XXXXX', function () {
        $kode = $this->service->generateKodeTransaksi();

        expect($kode)->toMatch('/^TRK-\d{8}-[A-Z0-9]{5}$/');
    });

    it('menghasilkan kode transaksi dengan tanggal hari ini', function () {
        $kode = $this->service->generateKodeTransaksi();
        $today = now()->format('Ymd');

        expect($kode)->toContain("TRK-{$today}-");
    });

    // ──── prosesTarik() ────

    it('mengurangi saldo nasabah setelah tarik berhasil', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000010']);

        $this->service->prosesTarik($nasabah, 20000, $petugas);

        expect($nasabah->fresh()->saldo)->toBe('30000.00');
    });

    it('membuat record transaksi_tariks', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000011']);

        $transaksi = $this->service->prosesTarik($nasabah, 15000, $petugas, 'Penarikan tunai');

        expect($transaksi)->toBeInstanceOf(TransaksiTarik::class);
        expect($transaksi->jumlah)->toBe('15000.00');
        expect($transaksi->keterangan)->toBe('Penarikan tunai');
        expect($transaksi->nasabah_id)->toBe($nasabah->id);
        expect($transaksi->petugas_id)->toBe($petugas->id);
    });

    it('menolak penarikan jika saldo tidak mencukupi', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 10000, 'no_hp' => '081200000012']);

        expect(fn () => $this->service->prosesTarik($nasabah, 20000, $petugas))
            ->toThrow(ValidationException::class);

        // Pastikan saldo tidak berubah
        expect($nasabah->fresh()->saldo)->toBe('10000.00');
    });

    it('error message berisi informasi saldo saat ini', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 10000, 'no_hp' => '081200000013']);

        try {
            $this->service->prosesTarik($nasabah, 20000, $petugas);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            expect($errors)->toHaveKey('jumlah');
            expect($errors['jumlah'][0])->toContain('Saldo tidak mencukupi');
        }
    });

    it('berhasil tarik tepat sebesar saldo (boundary)', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 25000, 'no_hp' => '081200000014']);

        $transaksi = $this->service->prosesTarik($nasabah, 25000, $petugas);

        expect($transaksi)->toBeInstanceOf(TransaksiTarik::class);
        expect($nasabah->fresh()->saldo)->toBe('0.00');
    });

    it('menyimpan keterangan null jika tidak disediakan', function () {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $nasabah = Nasabah::factory()->create(['saldo' => 50000, 'no_hp' => '081200000015']);

        $transaksi = $this->service->prosesTarik($nasabah, 10000, $petugas);

        expect($transaksi->keterangan)->toBeNull();
    });
});
