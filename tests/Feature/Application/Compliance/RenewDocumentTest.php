<?php

namespace Tests\Feature\Application\Compliance;

use App\Application\Compliance\RenewDocument;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    // Sios never had a booted() guard equivalent to Silos::booted()'s
    // "only one active per equipment", so RenewDocument itself places no
    // restriction on early renewal.
    //
    // The other early-SIO-renewal restriction this milestone once had —
    // a page-level guard in SiosResource/Pages/CreateSios::beforeCreate()
    // blocking a second active SIO for the same operator — was removed in
    // Milestone M2.7, the SIO-side counterpart to Silos::booted()'s
    // removal below.
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

    // TECHNICAL_AUDIT.md H2, closed: Silos::booted() used to block
    // creating a second active SILO for the same equipment, forcing
    // renewal to wait until after expiry. Milestone M2.7 removed that
    // persistence-level guard, matching
    // test_early_sio_renewal_already_works_today above.
    public function test_h2_early_silo_renewal_now_allowed(): void
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

        $renewed = (new RenewDocument)->handle($renewal);

        $this->assertTrue($renewed->exists);
        $this->assertDatabaseHas('silos', ['nomor_silo' => 'SILO-EARLY-RENEWAL']);
    }
}
