<?php

namespace Tests\Feature\Application\Operations;

use App\Application\Operations\EndAssignment;
use App\Models\OperatorAlatAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndAssignmentTest extends TestCase
{
    use RefreshDatabase;

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
}
