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
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5: this is
 * the unattended automation the audit named directly — "sistem merusak
 * data secara diam-diam, setiap malam, tanpa jejak apapun." Contrast with
 * AutoEndIneligibleAssignmentsTest (behavior, already covered) — this
 * file covers ARCHITECTURE_BLUEPRINT.md §8.8's three required log lines:
 * `info` at start, `notice` per assignment ended (with the specific
 * reason), `info` with processed/ended counts at completion.
 */
class AutoEndIneligibleAssignmentsLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Log::spy();
    }

    private function action(): AutoEndIneligibleAssignments
    {
        return new AutoEndIneligibleAssignments(new DocumentValidityMapper);
    }

    public function test_a_run_that_ends_an_assignment_logs_start_notice_and_completion(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $ended = $this->action()->handle(now());

        $this->assertSame(1, $ended);

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context) => $message === 'scheduler.auto_end_ineligible_assignments.started'
                && $context['active_assignment_count'] === 1
        )->atLeast()->once();

        Log::shouldHaveReceived('notice')->withArgs(
            function (string $message, array $context) use ($assignment) {
                return $message === 'assignment.auto_ended'
                    && $context['assignment_id'] === $assignment->getKey()
                    && $context['reasons'] === ['operator has no currently valid SIO'];
            }
        )->atLeast()->once();

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context) => $message === 'scheduler.auto_end_ineligible_assignments.completed'
                && $context['processed'] === 1
                && $context['ended'] === 1
        )->atLeast()->once();
    }

    public function test_reports_both_reasons_when_neither_document_is_valid(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create([
            'tanggal_terbit' => now()->subMonths(14)->toDateString(),
        ]);

        $assignment = OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $this->action()->handle(now());

        Log::shouldHaveReceived('notice')->withArgs(
            function (string $message, array $context) use ($assignment) {
                return $message === 'assignment.auto_ended'
                    && $context['assignment_id'] === $assignment->getKey()
                    && $context['reasons'] === [
                        'operator has no currently valid SIO',
                        'equipment has no currently valid SILO',
                    ];
            }
        )->atLeast()->once();
    }

    public function test_a_run_that_ends_nothing_still_logs_start_and_completion_but_no_notice(): void
    {
        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        OperatorAlatAssignment::factory()
            ->for($operator, 'operator')
            ->for($alatBerat, 'alatBerat')
            ->create(['is_active' => true]);

        $ended = $this->action()->handle(now());

        $this->assertSame(0, $ended);

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context) => $message === 'scheduler.auto_end_ineligible_assignments.completed'
                && $context['processed'] === 1
                && $context['ended'] === 0
        )->atLeast()->once();

        Log::shouldNotHaveReceived('notice');
    }
}
