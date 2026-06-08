<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaksi_setor_id', 'jenis_sampah_id', 'berat_kg', 'harga_saat_itu', 'subtotal'])]
class DetailSetorSampah extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'berat_kg' => 'decimal:2',
            'harga_saat_itu' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    // ──── Relationships ────

    public function transaksiSetor(): BelongsTo
    {
        return $this->belongsTo(TransaksiSetor::class);
    }

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class);
    }
}
