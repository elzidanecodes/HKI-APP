<?php

namespace Tests\Feature\Application\Operations;

use App\Application\Operations\EndAssignment;
use App\Models\OperatorAlatAssignment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndAssignmentTest extends TestCase
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

    public function test_ends_an_active_assignment(): void
    {
        $assignment = OperatorAlatAssignment::factory()->create(['is_active' => true]);

        $ended = (new EndAssignment)->handle($assignment, now());

        $this->assertFalse((bool) $ended->is_active);
        $this->assertNotNull($ended->tanggal_selesai);
        $this->assertDatabaseHas('operator_alat_assignments', [
            'id' => $assignment->id,
            'is_active' => false,
        ]);
    }

    // IMPLEMENTATION_PLAN.md Milestone M3.3's own testing requirement:
    // the Action-level check must fire even if a hypothetical caller
    // bypasses the UI entirely. LOGISTIK has no access to Operators per
    // the approved authorization matrix.
    public function test_logistik_cannot_end_an_assignment_even_calling_the_action_directly(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'LOGISTIK']));

        $assignment = OperatorAlatAssignment::factory()->create(['is_active' => true]);

        $this->expectException(AuthorizationException::class);

        (new EndAssignment)->handle($assignment, now());
    }
}
