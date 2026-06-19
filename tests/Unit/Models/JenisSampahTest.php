<?php

use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('JenisSampah Model', function () {

    // ──── Fillable (via Attribute) ────

    it('memiliki atribut yang dapat diisi', function () {
        $jenis = new JenisSampah();
        $fillable = $jenis->getFillable();

        expect($fillable)->toContain('nama');
        expect($fillable)->toContain('harga_per_kg');
        expect($fillable)->toContain('kategori_id');
        expect($fillable)->toContain('is_active');
    });

    // ──── Casts ────

    it('meng-cast harga_per_kg sebagai decimal', function () {
        $kategori = KategoriSampah::factory()->create();
        $jenis = JenisSampah::factory()->create([
            'harga_per_kg' => 1500,
            'kategori_id' => $kategori->id,
        ]);

        expect($jenis->harga_per_kg)->toBe('1500.00');
    });

    it('meng-cast is_active sebagai boolean', function () {
        $kategori = KategoriSampah::factory()->create();
        $jenis = JenisSampah::factory()->create([
            'is_active' => true,
            'kategori_id' => $kategori->id,
        ]);

        expect($jenis->is_active)->toBeTrue();
        expect($jenis->is_active)->toBeBool();
    });

    // ──── Relationships ────

    it('memiliki relasi belongsTo ke KategoriSampah', function () {
        $kategori = KategoriSampah::factory()->create(['nama' => 'Anorganik']);
        $jenis = JenisSampah::factory()->create([
            'kategori_id' => $kategori->id,
        ]);

        expect($jenis->kategori)->toBeInstanceOf(KategoriSampah::class);
        expect($jenis->kategori->nama)->toBe('Anorganik');
    });

    it('memiliki relasi hasMany ke DetailSetorSampah', function () {
        $kategori = KategoriSampah::factory()->create();
        $jenis = JenisSampah::factory()->create([
            'kategori_id' => $kategori->id,
        ]);

        expect($jenis->detailSetorSampahs())->toBeInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class
        );
    });

    // ──── Factory ────

    it('dapat dibuat via factory', function () {
        $kategori = KategoriSampah::factory()->create();
        $jenis = JenisSampah::factory()->create([
            'nama' => 'Botol Plastik',
            'harga_per_kg' => 2000,
            'kategori_id' => $kategori->id,
        ]);

        expect($jenis)->toBeInstanceOf(JenisSampah::class);
        expect($jenis->nama)->toBe('Botol Plastik');
        expect($jenis->harga_per_kg)->toBe('2000.00');
    });

    it('default is_active adalah true', function () {
        $kategori = KategoriSampah::factory()->create();
        $jenis = JenisSampah::factory()->create([
            'kategori_id' => $kategori->id,
        ]);

        expect($jenis->is_active)->toBeTrue();
    });
});
