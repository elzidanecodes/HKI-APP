<?php

namespace Database\Factories;

use App\Application\Compliance\SiloExpiryCalculator;
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
            'tanggal_expired' => (clone $tanggalTerbit)->modify('+1 year')->format('Y-m-d'),
            'file_path' => null,
        ];
    }

    /**
     * Silos::booted() previously recomputed tanggal_expired from the
     * final tanggal_terbit on every save (removed in IMPLEMENTATION_PLAN.md
     * Milestone M2.7). Tests across the suite override tanggal_terbit
     * alone and rely on tanggal_expired following it — this keeps that
     * working without going through IssueDocument/RenewDocument.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Silos $silo) {
            if ($silo->tanggal_terbit) {
                $silo->tanggal_expired = SiloExpiryCalculator::expiresAt($silo->tanggal_terbit);
            }
        });
    }
}
