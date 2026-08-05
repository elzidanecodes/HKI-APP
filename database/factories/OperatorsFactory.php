<?php

namespace Database\Factories;

use App\Models\Operators;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperatorsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Operators::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_operator' => $this->faker->name(),
            'nomor_hp' => '08'.$this->faker->numerify('##########'),
        ];
    }
}
