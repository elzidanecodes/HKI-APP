<?php

namespace Tests\Feature\Application\Operations;

use App\Application\Compliance\DocumentValidityMapper;
use App\Application\Operations\AutoEndIneligibleAssignments;
use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Application-layer fix for TECHNICAL_AUDIT.md C1. Contrast with
 * tests/Feature/Console/AutoEndExpiredAssignmentsTest.php (Milestone
 * M1.3), which characterizes the *current*, still-buggy console command —
 * these tests prove the replacement resolves the same scenarios
 * correctly. The old command is untouched here; it's cut over in
 * Milestone M2.5.
 */
class AutoEndIneligibleAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    private function action(): AutoEndIneligibleAssignments
    {
        return new AutoEndIneligibleAssignments(new DocumentValidityMapper);
    }

    private function activeAssignment(Operators $operator, AlatBerats $alatBerat): OperatorAlatAssignment
    {
        return OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);
    }

    public function test_ends_assignment_when_operator_has_no_valid_sio_at_all(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $assignment = $this->activeAssignment($operator, $alatBerat);

        $ended = $this->action()->handle(now());

        $this->assertSame(1, $ended);
        $this->assertFalse((bool) $assignment->fresh()->is_active);
    }

    public function test_ends_assignment_when_equipment_has_no_valid_silo_at_all(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(),
        ]);

        $assignment = $this->activeAssignment($operator, $alatBerat);

        $ended = $this->action()->handle(now());

        $this->assertSame(1, $ended);
        $this->assertFalse((bool) $assignment->fresh()->is_active);
    }

    public function test_leaves_assignment_active_when_both_documents_are_valid(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $assignment = $this->activeAssignment($operator, $alatBerat);

        $ended = $this->action()->handle(now());

        $this->assertSame(0, $ended);
        $this->assertTrue((bool) $assignment->fresh()->is_active);
    }

    public function test_does_not_touch_assignments_that_are_already_inactive(): void
    {
        $operator = Operators::factory()->create();
        // No valid SIO — would be ended if active.
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $originalEndDate = now()->subWeek()->toDateString();

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => false, 'tanggal_selesai' => $originalEndDate]);

        $ended = $this->action()->handle(now());

        $this->assertSame(0, $ended);
        // tanggal_selesai is cast to a Carbon date as of Milestone M5.1
        // (TECHNICAL_AUDIT.md M8) — was a raw string when this test was
        // first written.
        $this->assertSame($originalEndDate, $assignment->fresh()->tanggal_selesai->toDateString());
    }

    // TECHNICAL_AUDIT.md C1, resolved: an operator with one expired SIO
    // (kept for audit history) and one renewed, valid SIO keeps their
    // assignment active — contrast with
    // tests/Feature/Console/AutoEndExpiredAssignmentsTest::test_c1_assignment_incorrectly_ends_after_sio_renewal,
    // which proves the *current* command gets this same scenario wrong.
    public function test_c1_assignment_stays_active_after_sio_renewal(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subMonths(6)->toDateString(),
        ]);
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->addMonths(6)->toDateString(),
        ]);

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $assignment = $this->activeAssignment($operator, $alatBerat);

        $ended = $this->action()->handle(now());

        $this->assertSame(0, $ended);
        $this->assertTrue((bool) $assignment->fresh()->is_active);
    }

    // Same defect, SILO side — contrast with
    // tests/Feature/Console/AutoEndExpiredAssignmentsTest::test_c1_assignment_incorrectly_ends_after_silo_renewal.
    public function test_c1_assignment_stays_active_after_silo_renewal(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(),
        ]);
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonth()->toDateString(),
        ]);

        $assignment = $this->activeAssignment($operator, $alatBerat);

        $ended = $this->action()->handle(now());

        $this->assertSame(0, $ended);
        $this->assertTrue((bool) $assignment->fresh()->is_active);
    }
}
