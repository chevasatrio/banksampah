<?php

namespace App\Models;

use Database\Factories\NasabahFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nasabah extends Model
{
    /** @use HasFactory<NasabahFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_anggota',
        'nama',
        'nik',
        'alamat',
        'no_hp',
        'saldo',
        'is_active',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ──── Auto-generate no_anggota ────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Nasabah $nasabah) {
            if (empty($nasabah->no_anggota)) {
                $nasabah->no_anggota = self::generateNoAnggota();
            }
        });
    }

    /**
     * Generate a unique member number in format NSB-XXXXX.
     */
    public static function generateNoAnggota(): string
    {
        $lastNasabah = self::orderByDesc('id')->first();
        $nextNumber = $lastNasabah ? ((int) substr($lastNasabah->no_anggota, 4)) + 1 : 1;

        return 'NSB-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    // ──── Relationships ────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaksiSetors(): HasMany
    {
        return $this->hasMany(TransaksiSetor::class);
    }

    public function transaksiTariks(): HasMany
    {
        return $this->hasMany(TransaksiTarik::class);
    }
}
