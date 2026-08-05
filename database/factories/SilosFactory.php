<?php

namespace Database\Factories;

use App\Models\AlatBerats;
use App\Models\Silos;
use Illuminate\Database\Eloquent\Factories\Factory;

class SilosFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Silos::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalTerbit = $this->faker->dateTimeBetween('-11 months', 'now');

        return [
            'alat_berat_id' => AlatBerats::factory(),
            'nomor_silo' => 'SILO-'.$this->faker->unique()->numberBetween(1000, 9999),
            'tanggal_terbit' => $tanggalTerbit->format('Y-m-d'),
            // Overwritten by Silos::booted() on save (tanggal_terbit + 1 year);
            // set here too so the factory produces valid data on its own.
            'tanggal_expired' => (clone $tanggalTerbit)->modify('+1 year')->format('Y-m-d'),
            'file_path' => null,
        ];
    }
}
