<?php

namespace Database\Factories;

use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisSampah>
 */
class JenisSampahFactory extends Factory
{
    protected $model = JenisSampah::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->randomElement([
                'Botol Plastik', 'Kertas HVS', 'Kardus', 'Kaleng Aluminium',
                'Besi Tua', 'Plastik PET', 'Koran Bekas', 'Botol Kaca',
            ]),
            'harga_per_kg' => fake()->randomElement([500, 1000, 1500, 2000, 2500, 3000, 5000]),
            'kategori_id' => KategoriSampah::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Set the waste type as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
