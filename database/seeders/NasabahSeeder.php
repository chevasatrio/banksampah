<?php

namespace Database\Seeders;

use App\Models\Nasabah;
use Illuminate\Database\Seeder;

class NasabahSeeder extends Seeder
{
    /**
     * Seed the nasabahs table with sample members.
     */
    public function run(): void
    {
        $nasabahs = [
            [
                'nama' => 'Budi Santoso',
                'nik' => '3578011234567890',
                'alamat' => 'Jl. Mawar No. 5, RT 03/RW 02, Kel. Sukajadi, Surabaya',
                'no_hp' => '081234567890',
                'saldo' => 25000,
            ],
            [
                'nama' => 'Siti Rahayu',
                'nik' => '3578015005800001',
                'alamat' => 'Jl. Kenanga No. 12, RT 01/RW 05, Kel. Mulyorejo, Surabaya',
                'no_hp' => '081298765432',
                'saldo' => 50000,
            ],
            [
                'nama' => 'Ahmad Yani',
                'nik' => '3578012305750003',
                'alamat' => 'Jl. Melati No. 8, RT 02/RW 01, Kel. Gubeng, Surabaya',
                'no_hp' => '082345678901',
                'saldo' => 15000,
            ],
            [
                'nama' => 'Dewi Sartika',
                'nik' => '3578014506850004',
                'alamat' => 'Jl. Anggrek No. 3, RT 04/RW 03, Kel. Wonokromo, Surabaya',
                'no_hp' => '085678901234',
                'saldo' => 75000,
            ],
            [
                'nama' => 'Rudi Hermawan',
                'nik' => '3578011207900005',
                'alamat' => 'Jl. Dahlia No. 15, RT 05/RW 04, Kel. Tegalsari, Surabaya',
                'no_hp' => '087890123456',
                'saldo' => 0,
            ],
        ];

        foreach ($nasabahs as $data) {
            Nasabah::create($data);
        }
    }
}
