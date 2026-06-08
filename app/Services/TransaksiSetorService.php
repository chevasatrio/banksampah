<?php

namespace App\Services;

use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Models\TransaksiSetor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransaksiSetorService
{
    /**
     * Calculate total value from an array of items.
     *
     * @param  array<int, array{berat_kg: float, harga_per_kg: float}>  $items
     */
    public function hitungTotal(array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item['berat_kg'] * $item['harga_per_kg'];
        }

        return round($total, 2);
    }

    /**
     * Generate a unique transaction code.
     * Format: STR-YYYYMMDD-XXXXX
     */
    public function generateKodeTransaksi(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5));

        $kode = "STR-{$date}-{$random}";

        // Ensure uniqueness
        while (TransaksiSetor::where('kode_transaksi', $kode)->exists()) {
            $random = strtoupper(Str::random(5));
            $kode = "STR-{$date}-{$random}";
        }

        return $kode;
    }

    /**
     * Process a deposit transaction.
     * Creates the transaction, detail items, and updates the nasabah's balance.
     * Wrapped in a DB transaction for data integrity.
     *
     * @param  Nasabah  $nasabah
     * @param  array<int, array{jenis_sampah_id: int, berat_kg: float}>  $items
     * @param  User     $petugas
     * @param  string|null  $catatan
     * @return TransaksiSetor
     */
    public function prosesSetor(Nasabah $nasabah, array $items, User $petugas, ?string $catatan = null): TransaksiSetor
    {
        return DB::transaction(function () use ($nasabah, $items, $petugas, $catatan) {
            // Build detail items with current prices
            $detailItems = [];
            $totalNilai = 0;

            foreach ($items as $item) {
                $jenisSampah = JenisSampah::findOrFail($item['jenis_sampah_id']);
                $subtotal = round($item['berat_kg'] * $jenisSampah->harga_per_kg, 2);

                $detailItems[] = [
                    'jenis_sampah_id' => $jenisSampah->id,
                    'berat_kg' => $item['berat_kg'],
                    'harga_saat_itu' => $jenisSampah->harga_per_kg,
                    'subtotal' => $subtotal,
                ];

                $totalNilai += $subtotal;
            }

            // Create the transaction header
            $transaksi = TransaksiSetor::create([
                'kode_transaksi' => $this->generateKodeTransaksi(),
                'nasabah_id' => $nasabah->id,
                'petugas_id' => $petugas->id,
                'total_nilai' => $totalNilai,
                'catatan' => $catatan,
            ]);

            // Create detail items
            foreach ($detailItems as $detail) {
                $transaksi->detailSetorSampahs()->create($detail);
            }

            // Update nasabah balance
            $nasabah->increment('saldo', $totalNilai);

            return $transaksi;
        });
    }
}
