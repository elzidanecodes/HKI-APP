<?php

namespace Tests\Feature\Console;

use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * As of Milestone M2.5, AutoEndExpiredAssignments is a thin wrapper
 * around App\Application\Operations\AutoEndIneligibleAssignments — the
 * tests prefixed test_c1_* below were rewritten at this point (as
 * IMPLEMENTATION_PLAN.md M2.5 requires) to assert the corrected
 * behavior, now that it runs end-to-end through the real console
 * command, not just the Application-layer unit
 * (tests/Feature/Application/Operations/AutoEndIneligibleAssignmentsTest.php,
 * Milestone M2.4) or the Domain unit
 * (tests/Unit/Domain/Operations/AssignmentEligibilityTest.php,
 * Milestone M2.3). TECHNICAL_AUDIT.md finding C1 is closed.
 */
class AutoEndExpiredAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_ends_when_operator_has_no_valid_sio_at_all(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create(); // valid

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $this->artisan('assignment:auto-end-expired');

        $this->assertFalse((bool) $assignment->fresh()->is_active);
    }

    public function test_assignment_ends_when_equipment_has_no_valid_silo_at_all(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create(); // valid

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(), // expired
        ]);

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $this->artisan('assignment:auto-end-expired');

        $this->assertFalse((bool) $assignment->fresh()->is_active);
    }

    public function test_assignment_stays_active_when_operator_has_only_a_valid_sio(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create(); // valid, no other SIO on file

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create(); // valid

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $this->artisan('assignment:auto-end-expired');

        $this->assertTrue((bool) $assignment->fresh()->is_active);
    }

    // TECHNICAL_AUDIT.md C1, resolved: an operator with one expired SIO
    // (kept for audit history) and one renewed, valid SIO keeps their
    // assignment active. Before Milestone M2.5, this command asked "does
    // an EXPIRED SIO exist?" instead of "does NO VALID SIO exist?" and
    // incorrectly ended this exact assignment.
    public function test_c1_assignment_stays_active_after_sio_renewal(): void
    {
        $operator = Operators::factory()->create();
        // Old SIO, expired — kept on file for audit history.
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subMonths(6)->toDateString(),
        ]);
        // Renewed SIO, currently valid.
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->addMonths(6)->toDateString(),
        ]);

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create(); // valid

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $this->artisan('assignment:auto-end-expired');

        // The operator has a currently valid SIO, so this assignment is
        // legitimate and must remain active.
        $this->assertTrue((bool) $assignment->fresh()->is_active);
    }

    // Same fix, SILO side. Silos::booted() still forces the previous SILO
    // to already be expired before a new one can be created for the same
    // equipment (TECHNICAL_AUDIT.md H2, closed in Milestone M2.7, not
    // yet), so "one expired + one valid" remains the only reachable state
    // after a SILO renewal today — but the command now resolves it
    // correctly regardless.
    public function test_c1_assignment_stays_active_after_silo_renewal(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create(); // valid

        $alatBerat = AlatBerats::factory()->create();
        // Old SILO, expired — kept on file for audit history.
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(),
        ]);
        // Renewed SILO, currently valid.
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonth()->toDateString(),
        ]);

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $this->artisan('assignment:auto-end-expired');

        // The equipment has a currently valid SILO, so this assignment is
        // legitimate and must remain active.
        $this->assertTrue((bool) $assignment->fresh()->is_active);
    }
}
