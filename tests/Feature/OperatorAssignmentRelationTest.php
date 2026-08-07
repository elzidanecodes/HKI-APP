<?php

namespace Tests\Feature;

use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test for TECHNICAL_AUDIT.md M2 / IMPLEMENTATION_PLAN.md
 * Milestone M5.1: without an explicit foreign key, Laravel inferred one
 * from Operators' (plural) class name — "operators_id" — instead of the
 * real "operator_id" column, so Operators::assignments()/
 * activeAssignment() were latent-broken: they had never been called from
 * anywhere reachable, so the defect never surfaced as an error. This is
 * the first real test of these relations.
 */
class OperatorAssignmentRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignments_resolves_the_operators_assignment_history(): void
    {
        $operator = Operators::factory()->create();
        $alatBerat = AlatBerats::factory()->create();

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create();

        $this->assertTrue($operator->assignments()->exists());
        $this->assertSame($assignment->id, $operator->assignments()->first()->id);
    }

    public function test_active_assignment_resolves_the_operators_current_assignment(): void
    {
        $operator = Operators::factory()->create();
        $alatBerat = AlatBerats::factory()->create();

        OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => false]);

        $active = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for(AlatBerats::factory(), 'alatBerat')
            ->create(['is_active' => true]);

        $this->assertNotNull($operator->activeAssignment);
        $this->assertSame($active->id, $operator->activeAssignment->id);
    }
}
