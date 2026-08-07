<?php

namespace Tests\Feature\Application\Operations;

use App\Application\Compliance\DocumentValidityMapper;
use App\Application\Operations\AssignOperator;
use App\Domain\Operations\Exceptions\AssignmentIneligible;
use App\Domain\Operations\Services\AssignmentEligibility;
use App\Models\AlatBerats;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5:
 * ARCHITECTURE_BLUEPRINT.md §8.8 requires every state-changing operation
 * to log who/what/when/why at `info`. AssignOperator previously logged
 * nothing on success.
 */
class AssignOperatorLoggingTest extends TestCase
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

    private function action(): AssignOperator
    {
        return new AssignOperator(new AssignmentEligibility, new DocumentValidityMapper);
    }

    public function test_a_successful_assignment_logs_who_what_and_why(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $assignment = $this->action()->handle($alatBerat, $operator, now());

        Log::shouldHaveReceived('info')->withArgs(
            function (string $message, array $context) use ($assignment, $alatBerat, $operator) {
                return $message === 'assignment.created'
                    && $context['assignment_id'] === $assignment->getKey()
                    && $context['alat_berat_id'] === $alatBerat->id
                    && $context['operator_id'] === $operator->id
                    && $context['user_id'] === $this->user->id;
            }
        )->atLeast()->once();
    }

    // A rejected assignment must not be logged as though it succeeded —
    // "assignment.created" would be a false audit trail entry. The
    // authorized-but-rejected path still logs at 'info' via the Policy
    // layer's own allow decisions (LogsPolicyDecisions), so this asserts
    // specifically that no *assignment.created* line was ever written,
    // not that 'info' was never called at all. The rejection itself is
    // reported separately (Handler::reportable(), ExceptionReportingTest),
    // not duplicated here.
    public function test_a_rejected_assignment_does_not_log_assignment_created(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        // No SILO — ineligible.

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        try {
            $this->action()->handle($alatBerat, $operator, now());
            $this->fail('Expected AssignmentIneligible to be thrown.');
        } catch (AssignmentIneligible $exception) {
            // Expected.
        }

        Log::shouldNotHaveReceived('info', ['assignment.created', Mockery::any()]);
    }
}
