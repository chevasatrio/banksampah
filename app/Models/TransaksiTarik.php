<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kode_transaksi', 'nasabah_id', 'petugas_id', 'jumlah', 'keterangan'])]
class TransaksiTarik extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
        ];
    }

    // ──── Relationships ────

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
