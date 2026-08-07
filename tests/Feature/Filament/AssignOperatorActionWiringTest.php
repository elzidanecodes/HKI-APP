<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AlatBeratsResource\Pages\EditAlatBerats;
use App\Filament\Resources\AlatBeratsResource\RelationManagers\OperatorAssignmentsRelationManager;
use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression test (found during Milestone M4.4's investigation, fixed as
 * a standalone regression fix, not a new milestone).
 *
 * The "Assign Operator" header action used to fall through to Filament's
 * own default create() after ->before()'s eligibility check passed,
 * never calling AssignOperator::handle() — so its authorize('update',
 * $operator) call never ran. Confirmed before this fix: a LOGISTIK user
 * (denied all access to Operators by OperatorPolicy) could successfully
 * create an assignment through this exact screen. ->using() now routes
 * the write through the real Action instead.
 *
 * AssignmentGuardTest.php's existing coverage of the four eligibility
 * guards (SILO active, equipment idle, operator idle, SIO active) is
 * untouched by this fix and continues to pass unmodified — those guards
 * are evaluated by ->before(), which this fix does not change.
 */
class AssignOperatorActionWiringTest extends TestCase
{
    use RefreshDatabase;

    private function eligibleAlatBeratAndOperator(): array
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        return [$alatBerat, $operator];
    }

    private function assignViaRelationManager(AlatBerats $alatBerat, Operators $operator): void
    {
        Livewire::test(OperatorAssignmentsRelationManager::class, [
            'ownerRecord' => $alatBerat,
            'pageClass' => EditAlatBerats::class,
        ])->callTableAction('create', null, [
            'operator_id' => $operator->id,
            'tanggal_mulai' => now()->toDateString(),
        ]);
    }

    public function test_logistik_cannot_create_an_assignment_through_the_relation_manager(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'LOGISTIK']));
        [$alatBerat, $operator] = $this->eligibleAlatBeratAndOperator();

        $this->assignViaRelationManager($alatBerat, $operator);

        $this->assertDatabaseMissing('operator_alat_assignments', [
            'alat_berat_id' => $alatBerat->id,
            'operator_id' => $operator->id,
        ]);
    }

    public function test_hsse_creating_an_assignment_writes_exactly_one_row(): void
    {
        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));
        [$alatBerat, $operator] = $this->eligibleAlatBeratAndOperator();

        $this->assignViaRelationManager($alatBerat, $operator);

        $this->assertSame(1, OperatorAlatAssignment::where('alat_berat_id', $alatBerat->id)
            ->where('operator_id', $operator->id)
            ->count());
    }

    // Filament's native create() never logs anything — Milestone M4.4's
    // `assignment.created` line only fires from inside
    // AssignOperator::handle() itself, so seeing it here is direct proof
    // the write went through the Action, not the default path.
    public function test_creating_an_assignment_goes_through_assign_operator_handle(): void
    {
        Log::spy();

        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));
        [$alatBerat, $operator] = $this->eligibleAlatBeratAndOperator();

        $this->assignViaRelationManager($alatBerat, $operator);

        Log::shouldHaveReceived('info')->withArgs(
            function (string $message, array $context) use ($alatBerat, $operator) {
                return $message === 'assignment.created'
                    && $context['alat_berat_id'] === $alatBerat->id
                    && $context['operator_id'] === $operator->id;
            }
        )->atLeast()->once();
    }
}
