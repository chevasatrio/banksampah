<?php

namespace Database\Seeders;

use App\Models\KategoriSampah;
use Illuminate\Database\Seeder;

class KategoriSampahSeeder extends Seeder
{
    /**
     * Seed the kategori_sampahs table with standard waste categories.
     */
    public function run(): void
    {
        $kategoris = [
            [
                'nama' => 'Organik',
                'deskripsi' => 'Sampah yang berasal dari makhluk hidup dan dapat terurai secara alami, seperti sisa makanan, daun, dan ranting.',
            ],
            [
                'nama' => 'Anorganik',
                'deskripsi' => 'Sampah yang tidak mudah terurai secara alami, seperti plastik, kertas, logam, dan kaca.',
            ],
            [
                'nama' => 'B3 (Bahan Berbahaya & Beracun)',
                'deskripsi' => 'Sampah yang mengandung bahan berbahaya dan beracun, seperti baterai, lampu neon, dan elektronik.',
            ],
        ];

        foreach ($kategoris as $kategori) {
            KategoriSampah::create($kategori);
        }
    }
}
