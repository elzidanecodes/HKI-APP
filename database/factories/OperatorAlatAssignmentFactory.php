<?php

namespace Database\Factories;

use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperatorAlatAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OperatorAlatAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operator_id' => Operators::factory(),
            'alat_berat_id' => AlatBerats::factory(),
            'tanggal_mulai' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'tanggal_selesai' => null,
            'is_active' => true,
        ];
    }
}
