<?php

namespace Tests\Feature\Application\Operations;

use App\Application\Compliance\DocumentValidityMapper;
use App\Application\Operations\AssignOperator;
use App\Domain\Operations\Exceptions\AssignmentIneligible;
use App\Domain\Operations\Services\AssignmentEligibility;
use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignOperatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // HSSE has full CRUD on Operators and Alat Berat
        // (IMPLEMENTATION_PLAN.md Milestone M3.3's approved authorization
        // matrix).
        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));
    }

    private function action(): AssignOperator
    {
        return new AssignOperator(new AssignmentEligibility, new DocumentValidityMapper);
    }

    public function test_creates_an_active_assignment_when_eligible(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $assignment = $this->action()->handle($alatBerat, $operator, now());

        $this->assertTrue($assignment->exists);
        $this->assertDatabaseHas('operator_alat_assignments', [
            'alat_berat_id' => $alatBerat->id,
            'operator_id' => $operator->id,
            'is_active' => true,
        ]);
    }

    public function test_throws_and_writes_nothing_when_equipment_silo_is_not_active(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        // No SILO at all.

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        try {
            $this->action()->handle($alatBerat, $operator, now());
            $this->fail('Expected AssignmentIneligible to be thrown.');
        } catch (AssignmentIneligible $exception) {
            $this->assertSame(['equipment_silo_not_active'], $exception->reasons());
        }

        $this->assertDatabaseCount('operator_alat_assignments', 0);
    }

    public function test_throws_when_operator_is_already_assigned_elsewhere(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();
        OperatorAlatAssignment::factory()->for($operator, 'operator')->create(['is_active' => true]);

        try {
            $this->action()->handle($alatBerat, $operator, now());
            $this->fail('Expected AssignmentIneligible to be thrown.');
        } catch (AssignmentIneligible $exception) {
            $this->assertSame(['operator_already_assigned_elsewhere'], $exception->reasons());
        }

        // Only the pre-existing assignment — nothing new was written.
        $this->assertDatabaseCount('operator_alat_assignments', 1);
    }

    // TECHNICAL_AUDIT.md C1, at the orchestration level: an operator with
    // one expired SIO (kept for audit history) and one renewed, valid SIO
    // must still be assignable.
    public function test_c1_operator_with_a_renewed_sio_is_still_assignable(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subMonths(6)->toDateString(),
        ]);
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->addMonths(6)->toDateString(),
        ]);

        $assignment = $this->action()->handle($alatBerat, $operator, now());

        $this->assertTrue($assignment->exists);
    }

    // IMPLEMENTATION_PLAN.md Milestone M3.3's own testing requirement:
    // the Action-level check must fire even if a hypothetical caller
    // bypasses the UI entirely. LOGISTIK has full CRUD on Alat Berat but
    // no access to Operators per the approved authorization matrix —
    // assigning an operator touches both, so this must still be denied,
    // not just checked against Alat Berat alone.
    public function test_logistik_cannot_assign_an_operator_even_calling_the_action_directly(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'LOGISTIK']));

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $this->expectException(AuthorizationException::class);

        $this->action()->handle($alatBerat, $operator, now());
    }
}
