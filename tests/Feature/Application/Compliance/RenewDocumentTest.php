<?php

namespace Tests\Feature\Application\Compliance;

use App\Application\Compliance\RenewDocument;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RenewDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renews_a_sio_after_the_previous_one_expired(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $renewal = new Sios([
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-RENEWED-0001',
            'tanggal_expired' => now()->addYear()->toDateString(),
        ]);

        $renewed = (new RenewDocument)->handle($renewal);

        $this->assertTrue($renewed->exists);
        $this->assertDatabaseHas('sios', ['nomor_sio' => 'SIO-RENEWED-0001']);
    }

    // M9 asymmetry, illustrated at the Application layer specifically:
    // Sios::booted() has no guard equivalent to Silos::booted()'s "only
    // one active per equipment", so RenewDocument itself places no
    // restriction on early renewal.
    //
    // Correction discovered in Milestone M2.5: this does NOT mean early
    // SIO renewal is unrestricted end-to-end today. A separate,
    // page-level guard — SiosResource/Pages/CreateSios::beforeCreate() —
    // blocks creating a second active SIO for the same operator, the same
    // restriction H2 describes for SILO, just implemented at the Filament
    // page level instead of the model level. That guard is untouched by
    // this milestone (M2.5 only changes *how* it queries, not *whether*
    // it blocks) and is a newly-identified target for Milestone M2.7,
    // alongside Silos::booted().
    public function test_early_sio_renewal_already_works_today(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->addDays(10)->toDateString(), // still valid
        ]);

        $renewal = new Sios([
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-EARLY-RENEWAL',
            'tanggal_expired' => now()->addYear()->toDateString(),
        ]);

        $renewed = (new RenewDocument)->handle($renewal);

        $this->assertTrue($renewed->exists);
    }

    public function test_renews_a_silo_after_the_previous_one_expired(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(), // expired
        ]);

        $renewal = new Silos([
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-RENEWED-0001',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $renewed = (new RenewDocument)->handle($renewal);

        $this->assertTrue($renewed->exists);
        $this->assertDatabaseHas('silos', ['nomor_silo' => 'SILO-RENEWED-0001']);
    }

    // CHARACTERIZES the still-existing part of TECHNICAL_AUDIT.md H2 — DO
    // NOT treat as correct or final. RenewDocument itself adds no
    // restriction of its own, but Silos::booted() still blocks creating a
    // second active SILO for the same equipment; that persistence-level
    // guard is only removed in Milestone M2.7. Until then, an early SILO
    // renewal through this Action still throws. Once M2.7 lands, this
    // test must be rewritten to expect success, matching
    // test_early_sio_renewal_already_works_today above.
    public function test_h2_early_silo_renewal_still_blocked_today(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subDays(10)->toDateString(), // still valid
        ]);

        $renewal = new Silos([
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-EARLY-RENEWAL',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $this->expectException(ValidationException::class);

        (new RenewDocument)->handle($renewal);
    }
}
