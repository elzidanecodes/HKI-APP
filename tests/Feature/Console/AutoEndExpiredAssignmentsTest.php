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
 * Characterizes AutoEndExpiredAssignments exactly as it behaves today
 * (IMPLEMENTATION_PLAN.md Milestone M1.3). The tests prefixed test_c1_*
 * below CHARACTERIZE C1 — a known bug (TECHNICAL_AUDIT.md finding C1) —
 * and must NOT be read as a specification of correct behavior. Phase 2
 * (M2.2-M2.5) replaces this command's logic; when it does, these two
 * tests must be rewritten to assert the assignment stays active.
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

    // CHARACTERIZES C1 — DO NOT treat as correct behavior.
    //
    // TECHNICAL_AUDIT.md C1: the command checks "does an EXPIRED SIO exist
    // for this operator?" instead of "does NO VALID SIO exist?". Expired
    // documents are intentionally never deleted (kept for audit history),
    // so any operator who has ever renewed their SIO has one expired SIO
    // and one valid SIO on file at the same time. This proves the command
    // ends a still-legitimate assignment in exactly that situation — the
    // audit's own conclusion that this is "guaranteed to happen, not just
    // possible," starting from the very first renewal in the system.
    public function test_c1_assignment_incorrectly_ends_after_sio_renewal(): void
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

        // BUG: the operator has a currently valid SIO, so this assignment
        // is legitimate — but the command ends it anyway, because it only
        // checks whether an expired document *exists*, not whether a valid
        // one is missing.
        $this->assertFalse((bool) $assignment->fresh()->is_active);
    }

    // CHARACTERIZES C1 — DO NOT treat as correct behavior. Same defect,
    // SILO side. Silos::booted() forces the previous SILO to already be
    // expired before a new one can be created for the same equipment
    // (TECHNICAL_AUDIT.md H2), so "one expired + one valid" is in fact the
    // *only* reachable state after any SILO renewal today.
    public function test_c1_assignment_incorrectly_ends_after_silo_renewal(): void
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

        // BUG: the equipment has a currently valid SILO — this assignment
        // is legitimate, but the command ends it anyway, for the same
        // reason as the SIO case above.
        $this->assertFalse((bool) $assignment->fresh()->is_active);
    }
}
