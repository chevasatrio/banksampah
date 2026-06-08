<?php

namespace App\Models;

use Database\Factories\JenisSampahFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'harga_per_kg', 'kategori_id', 'is_active'])]
class JenisSampah extends Model
{
    /** @use HasFactory<JenisSampahFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_per_kg' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ──── Relationships ────

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_id');
    }

    public function detailSetorSampahs(): HasMany
    {
        return $this->hasMany(DetailSetorSampah::class);
    }
}
