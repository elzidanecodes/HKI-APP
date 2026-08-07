<?php

namespace Tests\Feature\Application\Operations;

use App\Application\Operations\EndAssignment;
use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5:
 * ARCHITECTURE_BLUEPRINT.md §8.8 requires every state-changing operation
 * to log who/what/when/why at `info`. EndAssignment previously logged
 * nothing on success. Logged with `reason: manual` to distinguish it
 * from AutoEndIneligibleAssignmentsLoggingTest's `assignment.auto_ended`
 * `notice` — same manual/automatic split as Blueprint §4 Diagram 3.
 */
class EndAssignmentLoggingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Log::spy();

        $this->user = User::factory()->create(['job_title' => 'HSSE']);
        $this->actingAs($this->user);
    }

    public function test_manually_ending_an_assignment_logs_who_what_and_why(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        $operator = Operators::factory()->create();

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $tanggalSelesai = now();

        (new EndAssignment)->handle($assignment, $tanggalSelesai);

        Log::shouldHaveReceived('info')->withArgs(
            function (string $message, array $context) use ($assignment, $alatBerat, $operator) {
                return $message === 'assignment.ended'
                    && $context['assignment_id'] === $assignment->getKey()
                    && $context['alat_berat_id'] === $alatBerat->id
                    && $context['operator_id'] === $operator->id
                    && $context['reason'] === 'manual'
                    && $context['user_id'] === $this->user->id;
            }
        )->atLeast()->once();
    }
}
