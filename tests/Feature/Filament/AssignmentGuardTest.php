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
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;
use Tests\TestCase;

/**
 * Characterizes the four assignment guards in
 * OperatorAssignmentsRelationManager exactly as they behave today
 * (IMPLEMENTATION_PLAN.md Milestone M1.3). This is the "before" snapshot
 * Phase 2 refactors against — it documents current behavior, not a claim
 * that this design is correct.
 */
class AssignmentGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // HSSE has full CRUD on every entity this test touches
        // (IMPLEMENTATION_PLAN.md Milestone M3.3's approved authorization
        // matrix) — a random job_title would make this test flaky now
        // that Policies are enforced, not just logged.
        $this->actingAs(User::factory()->create(['job_title' => 'HSSE']));
    }

    private function relationManager(AlatBerats $alatBerat): TestableLivewire
    {
        return Livewire::test(OperatorAssignmentsRelationManager::class, [
            'ownerRecord' => $alatBerat,
            'pageClass' => EditAlatBerats::class,
        ]);
    }

    public function test_assignment_succeeds_when_all_four_guards_pass(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();

        $this->relationManager($alatBerat)->callTableAction('create', null, [
            'operator_id' => $operator->id,
            'tanggal_mulai' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('operator_alat_assignments', [
            'alat_berat_id' => $alatBerat->id,
            'operator_id' => $operator->id,
            'is_active' => true,
        ]);
    }

    // Note: there is no "call the action and assert no record was created"
    // test for the SILO-inactive or equipment-already-assigned conditions.
    // Both are also covered by the header action's disabled() closure, and
    // Filament's own mountTableAction() returns as soon as isDisabled() is
    // true (vendor/filament/tables/src/Concerns/HasActions.php:167-169) —
    // before the action's form is even mounted, let alone its before()
    // guard evaluated. In current behavior, for these two conditions
    // specifically, it is the disabled state — not the matching before()
    // check inside the RelationManager — that actually prevents creation
    // through the normal action flow; the before() checks for these two
    // are redundant and unreachable that way. See the
    // test_create_action_is_disabled_when_* tests below for the guard that
    // is actually reachable and enforced for these two conditions.

    public function test_assignment_is_blocked_when_operator_is_already_active_elsewhere(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create();
        OperatorAlatAssignment::factory()->for($operator, 'operator')->create(['is_active' => true]);

        $this->relationManager($alatBerat)->callTableAction('create', null, [
            'operator_id' => $operator->id,
            'tanggal_mulai' => now()->toDateString(),
        ]);

        $this->assertDatabaseCount('operator_alat_assignments', 1);
    }

    public function test_assignment_is_blocked_when_operator_sio_is_not_active(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $operator = Operators::factory()->create();
        Sios::factory()->for($operator, 'operator')->create([
            'tanggal_expired' => now()->subDay()->toDateString(),
        ]);

        $this->relationManager($alatBerat)->callTableAction('create', null, [
            'operator_id' => $operator->id,
            'tanggal_mulai' => now()->toDateString(),
        ]);

        $this->assertDatabaseMissing('operator_alat_assignments', [
            'alat_berat_id' => $alatBerat->id,
        ]);
    }

    public function test_create_action_is_disabled_when_equipment_has_no_active_silo(): void
    {
        $alatBerat = AlatBerats::factory()->create();

        $this->relationManager($alatBerat)->assertTableActionDisabled('create');
    }

    public function test_create_action_is_disabled_when_equipment_already_has_an_active_assignment(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();
        OperatorAlatAssignment::factory()->for($alatBerat, 'alatBerat')->create(['is_active' => true]);

        $this->relationManager($alatBerat)->assertTableActionDisabled('create');
    }

    public function test_create_action_is_enabled_when_equipment_is_idle_with_an_active_silo(): void
    {
        $alatBerat = AlatBerats::factory()->create();
        Silos::factory()->for($alatBerat, 'alatBerat')->create();

        $this->relationManager($alatBerat)->assertTableActionEnabled('create');
    }
}
