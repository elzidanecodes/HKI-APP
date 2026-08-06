<?php

namespace Database\Factories;

use App\Models\Operators;
use App\Models\Sios;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiosFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sios::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operator_id' => Operators::factory(),
            'nomor_sio' => 'SIO-'.$this->faker->unique()->numberBetween(1000, 9999),
            // Real column as of Milestone M2.7 (closes part of
            // TECHNICAL_AUDIT.md M9 — previously no matching column existed).
            'tanggal_terbit' => $this->faker->dateTimeBetween('-11 months', 'now')->format('Y-m-d'),
            'tanggal_expired' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'file_sio' => null,
        ];
    }
}
