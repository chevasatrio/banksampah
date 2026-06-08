<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with default admin and petugas accounts.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Petugas Bank Sampah',
            'email' => 'petugas@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'email_verified_at' => now(),
        ]);
    }
}
