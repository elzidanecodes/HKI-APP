<?php

namespace Database\Factories;

use App\Models\AlatBerats;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlatBeratsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AlatBerats::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_alat' => strtoupper($this->faker->unique()->bothify('???-###')),
            'nama_alat' => $this->faker->randomElement(['Excavator', 'Bulldozer', 'Crane', 'Dump Truck', 'Motor Grader']),
            'merk_alat' => $this->faker->randomElement(['Komatsu', 'Caterpillar', 'Hitachi', 'Kobelco']),
            'tipe_alat' => $this->faker->bothify('PC-###'),
            'tahun_produksi' => $this->faker->year(),
            'sta_lokasi' => $this->faker->randomElement(['STA 12+500', 'Gudang A', 'Workshop']),
        ];
    }
}
