<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use Illuminate\Database\Seeder;

class JenisSampahSeeder extends Seeder
{
    /**
     * Seed the jenis_sampahs table with common waste types and prices.
     */
    public function run(): void
    {
        $organik = KategoriSampah::where('nama', 'Organik')->first();
        $anorganik = KategoriSampah::where('nama', 'Anorganik')->first();
        $b3 = KategoriSampah::where('nama', 'like', 'B3%')->first();

        // Organik
        $this->createJenis($organik->id, [
            ['nama' => 'Sisa Makanan', 'harga_per_kg' => 500],
            ['nama' => 'Daun & Ranting', 'harga_per_kg' => 300],
        ]);

        // Anorganik
        $this->createJenis($anorganik->id, [
            ['nama' => 'Botol Plastik (PET)', 'harga_per_kg' => 2000],
            ['nama' => 'Plastik Campuran', 'harga_per_kg' => 1000],
            ['nama' => 'Kertas HVS', 'harga_per_kg' => 1500],
            ['nama' => 'Koran Bekas', 'harga_per_kg' => 1200],
            ['nama' => 'Kardus', 'harga_per_kg' => 1800],
            ['nama' => 'Kaleng Aluminium', 'harga_per_kg' => 8000],
            ['nama' => 'Besi Tua', 'harga_per_kg' => 5000],
            ['nama' => 'Botol Kaca', 'harga_per_kg' => 500],
            ['nama' => 'Tembaga', 'harga_per_kg' => 50000],
            ['nama' => 'Kuningan', 'harga_per_kg' => 30000],
        ]);

        // B3
        $this->createJenis($b3->id, [
            ['nama' => 'Baterai Bekas', 'harga_per_kg' => 3000],
            ['nama' => 'Elektronik (E-Waste)', 'harga_per_kg' => 5000],
            ['nama' => 'Lampu Neon', 'harga_per_kg' => 1000],
        ]);
    }

    /**
     * Helper to create jenis sampah records for a given kategori.
     */
    private function createJenis(int $kategoriId, array $items): void
    {
        foreach ($items as $item) {
            JenisSampah::create([
                'nama' => $item['nama'],
                'harga_per_kg' => $item['harga_per_kg'],
                'kategori_id' => $kategoriId,
                'is_active' => true,
            ]);
        }
    }
}
