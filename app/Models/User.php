<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ──── Role Helpers ────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isNasabah(): bool
    {
        return $this->role === 'nasabah';
    }

    // ──── Relationships ────

    /**
     * Nasabah profile linked to this user account.
     */
    public function nasabah(): HasOne
    {
        return $this->hasOne(Nasabah::class);
    }

    /**
     * Deposit transactions processed by this user (as petugas).
     */
    public function transaksiSetorDiproses(): HasMany
    {
        return $this->hasMany(TransaksiSetor::class, 'petugas_id');
    }

    /**
     * Withdrawal transactions processed by this user (as petugas).
     */
    public function transaksiTarikDiproses(): HasMany
    {
        return $this->hasMany(TransaksiTarik::class, 'petugas_id');
    }
}
