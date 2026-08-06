<?php

namespace Tests\Feature\Application\Compliance;

use App\Application\Compliance\IssueDocument;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // HSSE has full CRUD on SIO/SILO (IMPLEMENTATION_PLAN.md
        // Milestone M3.3's approved authorization matrix).
        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));
    }

    public function test_issues_a_new_sio(): void
    {
        $operator = Operators::factory()->create();

        $sio = new Sios([
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-0001',
            'tanggal_expired' => now()->addYear()->toDateString(),
        ]);

        $issued = (new IssueDocument)->handle($sio);

        $this->assertTrue($issued->exists);
        $this->assertDatabaseHas('sios', [
            'operator_id' => $operator->id,
            'nomor_sio' => 'SIO-0001',
        ]);
    }

    public function test_issues_a_new_silo(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $silo = new Silos([
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-0001',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $issued = (new IssueDocument)->handle($silo);

        $this->assertTrue($issued->exists);
        $this->assertDatabaseHas('silos', [
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-0001',
        ]);
    }

    // IMPLEMENTATION_PLAN.md Milestone M3.3's own testing requirement:
    // the Action-level check must fire even if a hypothetical caller
    // bypasses the UI entirely. LOGISTIK has no access to SIO/SILO per
    // the approved authorization matrix — mirrors the audit's own praise
    // for the existing ->before() defense-in-depth pattern.
    public function test_logistik_cannot_issue_a_silo_even_calling_the_action_directly(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'LOGISTIK']));

        $alatBerat = AlatBerats::factory()->create();

        $silo = new Silos([
            'alat_berat_id' => $alatBerat->id,
            'nomor_silo' => 'SILO-0002',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $this->expectException(AuthorizationException::class);

        (new IssueDocument)->handle($silo);
    }
}
