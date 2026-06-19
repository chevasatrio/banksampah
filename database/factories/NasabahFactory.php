<?php

namespace Database\Factories;

use App\Models\Nasabah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nasabah>
 */
class NasabahFactory extends Factory
{
    protected $model = Nasabah::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake('id_ID')->name(),
            'nik' => fake()->unique()->numerify('################'),
            'alamat' => fake('id_ID')->address(),
            'no_hp' => fake()->numerify('08##########'),
            'saldo' => 0,
            'is_active' => true,
            'user_id' => null,
        ];
    }

    /**
     * Set the nasabah as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific saldo.
     */
    public function withSaldo(float $saldo): static
    {
        return $this->state(fn (array $attributes) => [
            'saldo' => $saldo,
        ]);
    }
}
