<?php

namespace Database\Factories;

use App\Models\KategoriSampah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriSampah>
 */
class KategoriSampahFactory extends Factory
{
    protected $model = KategoriSampah::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->randomElement(['Organik', 'Anorganik', 'B3', 'Elektronik', 'Logam']),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
