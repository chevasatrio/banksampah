<?php

use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Nasabah Model', function () {

    // ──── Fillable ────

    it('memiliki atribut yang dapat diisi (fillable)', function () {
        $nasabah = new Nasabah();
        $fillable = $nasabah->getFillable();

        expect($fillable)->toContain('nama');
        expect($fillable)->toContain('nik');
        expect($fillable)->toContain('alamat');
        expect($fillable)->toContain('no_hp');
        expect($fillable)->toContain('saldo');
        expect($fillable)->toContain('is_active');
        expect($fillable)->toContain('no_anggota');
    });

    // ──── Casts ────

    it('meng-cast saldo sebagai decimal', function () {
        $nasabah = Nasabah::factory()->create(['saldo' => 1000, 'no_hp' => '081200000050']);

        // decimal:2 cast returns string representation
        expect($nasabah->saldo)->toBe('1000.00');
    });

    it('meng-cast is_active sebagai boolean', function () {
        $nasabah = Nasabah::factory()->create(['is_active' => 1, 'no_hp' => '081200000051']);

        expect($nasabah->is_active)->toBeTrue();
        expect($nasabah->is_active)->toBeBool();
    });

    // ──── Auto-generate no_anggota ────

    it('saldo default adalah nol', function () {
        $nasabah = Nasabah::factory()->create(['no_hp' => '081200000052']);

        expect($nasabah->saldo)->toBe('0.00');
    });

    it('no_anggota dibuat secara otomatis saat create', function () {
        $nasabah = Nasabah::factory()->create(['no_hp' => '081200000053']);

        expect($nasabah->no_anggota)->not->toBeNull();
        expect($nasabah->no_anggota)->not->toBeEmpty();
    });

    it('no_anggota memiliki format NSB-XXXXX', function () {
        $nasabah = Nasabah::factory()->create(['no_hp' => '081200000054']);

        expect($nasabah->no_anggota)->toMatch('/^NSB-\d{5}$/');
    });

    it('no_anggota unik untuk setiap nasabah', function () {
        $nasabah1 = Nasabah::factory()->create(['no_hp' => '081200000055']);
        $nasabah2 = Nasabah::factory()->create(['no_hp' => '081200000056']);

        expect($nasabah1->no_anggota)->not->toBe($nasabah2->no_anggota);
    });

    it('no_anggota berurutan (increment)', function () {
        $nasabah1 = Nasabah::factory()->create(['no_hp' => '081200000057']);
        $nasabah2 = Nasabah::factory()->create(['no_hp' => '081200000058']);

        // NSB-00001, NSB-00002
        $num1 = (int) substr($nasabah1->no_anggota, 4);
        $num2 = (int) substr($nasabah2->no_anggota, 4);

        expect($num2)->toBe($num1 + 1);
    });

    // ──── Relationships ────

    it('memiliki relasi belongsTo ke User', function () {
        $user = User::factory()->create();
        $nasabah = Nasabah::factory()->create([
            'user_id' => $user->id,
            'no_hp' => '081200000059',
        ]);

        expect($nasabah->user)->toBeInstanceOf(User::class);
        expect($nasabah->user->id)->toBe($user->id);
    });

    it('memiliki relasi hasMany ke transaksiSetors', function () {
        $nasabah = Nasabah::factory()->create(['no_hp' => '081200000060']);

        expect($nasabah->transaksiSetors())->toBeInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class
        );
    });

    it('memiliki relasi hasMany ke transaksiTariks', function () {
        $nasabah = Nasabah::factory()->create(['no_hp' => '081200000061']);

        expect($nasabah->transaksiTariks())->toBeInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class
        );
    });
});
