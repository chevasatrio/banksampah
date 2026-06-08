<?php

namespace App\Services;

use App\Models\Nasabah;
use App\Models\TransaksiTarik;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransaksiTarikService
{
    /**
     * Generate a unique withdrawal transaction code.
     * Format: TRK-YYYYMMDD-XXXXX
     */
    public function generateKodeTransaksi(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5));

        $kode = "TRK-{$date}-{$random}";

        // Ensure uniqueness
        while (TransaksiTarik::where('kode_transaksi', $kode)->exists()) {
            $random = strtoupper(Str::random(5));
            $kode = "TRK-{$date}-{$random}";
        }

        return $kode;
    }

    /**
     * Process a withdrawal transaction.
     * Validates balance sufficiency, creates the transaction, and updates the nasabah's balance.
     * Wrapped in a DB transaction for data integrity.
     *
     * @param  Nasabah      $nasabah
     * @param  float        $jumlah
     * @param  User         $petugas
     * @param  string|null  $keterangan
     * @return TransaksiTarik
     *
     * @throws ValidationException if balance is insufficient
     */
    public function prosesTarik(Nasabah $nasabah, float $jumlah, User $petugas, ?string $keterangan = null): TransaksiTarik
    {
        return DB::transaction(function () use ($nasabah, $jumlah, $petugas, $keterangan) {
            // Re-read balance with lock to prevent race conditions
            $nasabah = Nasabah::lockForUpdate()->findOrFail($nasabah->id);

            // Validate sufficient balance
            if ($nasabah->saldo < $jumlah) {
                throw ValidationException::withMessages([
                    'jumlah' => "Saldo tidak mencukupi. Saldo saat ini: Rp " . number_format($nasabah->saldo, 0, ',', '.'),
                ]);
            }

            // Create the withdrawal transaction
            $transaksi = TransaksiTarik::create([
                'kode_transaksi' => $this->generateKodeTransaksi(),
                'nasabah_id' => $nasabah->id,
                'petugas_id' => $petugas->id,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
            ]);

            // Deduct nasabah balance
            $nasabah->decrement('saldo', $jumlah);

            return $transaksi;
        });
    }
}
