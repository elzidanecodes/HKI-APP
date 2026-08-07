<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'job_title' => $this->faker->randomElement(['HSSE', 'LOGISTIK', 'DOKON']),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * TECHNICAL_AUDIT.md L4 / IMPLEMENTATION_PLAN.md Milestone M5.2: this
     * used to import App\Models\Team, which doesn't exist — Jetstream's
     * Teams feature was never installed for this app
     * (config/jetstream.php: Features::teams() is commented out). The
     * Team::factory() branch was unreachable dead code guarded by
     * Features::hasTeamFeatures(), which is always false here, so
     * callers (EmailVerificationTest, PasswordConfirmationTest) were
     * already only ever hitting the no-op branch below. This keeps that
     * same no-op behavior without the dead import.
     */
    public function withPersonalTeam(): static
    {
        return $this->state([]);
    }
}
