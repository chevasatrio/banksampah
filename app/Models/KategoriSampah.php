<?php

namespace App\Models;

use Database\Factories\KategoriSampahFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'deskripsi'])]
class KategoriSampah extends Model
{
    /** @use HasFactory<KategoriSampahFactory> */
    use HasFactory;

    // ──── Relationships ────

    public function jenisSampahs(): HasMany
    {
        return $this->hasMany(JenisSampah::class, 'kategori_id');
    }
}
