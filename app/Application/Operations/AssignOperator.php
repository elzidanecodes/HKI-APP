<?php

namespace App\Application\Operations;

use App\Application\Compliance\DocumentValidityMapper;
use App\Domain\Operations\Exceptions\AssignmentIneligible;
use App\Domain\Operations\Services\AssignmentEligibility;
use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Assigns an operator to a piece of equipment — replacing the four
 * guards currently duplicated inside
 * OperatorAssignmentsRelationManager's ->before() closure
 * (TECHNICAL_AUDIT.md §7) with a single call to AssignmentEligibility
 * (Milestone M2.3). checkEligibility() was wired into that RelationManager's
 * ->before() guard at Milestone M2.5; handle() itself (the actual write,
 * with authorization/transaction/row-locking) was left unwired until a
 * regression found during Milestone M4.4's investigation — the modal was
 * still falling through to Filament's own default create() afterward,
 * which bypassed all three. Now wired via ->using().
 *
 * Authorization is enforced against both entities involved
 * (IMPLEMENTATION_PLAN.md Milestone M3.3): assigning an operator to
 * equipment touches both, so the approved authorization matrix's
 * "LOGISTIK: no access to Operators" must hold here even though LOGISTIK
 * has full CRUD on Alat Berat — checking Alat Berat alone would let
 * LOGISTIK assign operators despite that exclusion.
 *
 * checkEligibility()'s two assignment-existence checks are row-locked
 * inside handle()'s transaction (Milestone M4.2, closing TECHNICAL_AUDIT.md
 * H1): two near-simultaneous requests for the same operator or equipment
 * now serialize on those rows instead of racing to both pass the same
 * check before either write lands. The database's own unique constraint
 * (same milestone) is the actual last line of defense — the lock exists
 * to fail cleanly with AssignmentIneligible instead of an ugly
 * QueryException from the constraint. Document-validity checks
 * (SIO/SILO) are deliberately not locked: they're not the invariant this
 * migration protects, and locking unrelated tables would just add
 * contention. lockForUpdate() defaults to off so the UI guard
 * (OperatorAssignmentsRelationManager's before() closure, Milestone
 * M2.5) can keep calling checkEligibility() read-only, outside any
 * transaction.
 *
 * Deliberately queries OperatorAlatAssignment directly for the operator's
 * busy-elsewhere check rather than calling Operators::activeAssignment():
 * that relation infers its foreign key from the model name ("operators_id"
 * instead of the real "operator_id") — TECHNICAL_AUDIT.md finding M2,
 * fixed in Milestone M5.1, not here. The current RelationManager guard
 * already avoids it the same way; this Action does too.
 *
 * checkEligibility() is exposed separately from handle() so
 * OperatorAssignmentsRelationManager (Milestone M2.5) can reuse the exact
 * same check for its UI guard without writing — Filament may depend on
 * this Application Action (ARCHITECTURE_BLUEPRINT.md §8.2), but may not
 * import App\Domain\Operations\Services\AssignmentEligibility directly,
 * which is not an enum or value object.
 *
 * handle() logs at `info` after commit, not inside the transaction
 * closure (ARCHITECTURE_BLUEPRINT.md §8.4) — IMPLEMENTATION_PLAN.md
 * Milestone M4.4, closing TECHNICAL_AUDIT.md H5 for Operations.
 */
final class AssignOperator
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AssignmentEligibility $eligibility,
        private readonly DocumentValidityMapper $validities,
    ) {}

    /**
     * @return string[]
     */
    public function checkEligibility(AlatBerats $alatBerat, Operators $operator, CarbonInterface $today, bool $lockForUpdate = false): array
    {
        $equipmentAssignment = $alatBerat->activeAssignment();
        $operatorAssignmentElsewhere = OperatorAlatAssignment::where('operator_id', $operator->id)
            ->where('is_active', true);

        if ($lockForUpdate) {
            $equipmentAssignment->lockForUpdate();
            $operatorAssignmentElsewhere->lockForUpdate();
        }

        return $this->eligibility->ineligibilityReasons(
            operatorSioValidities: $this->validities->forOperatorSios($operator),
            equipmentSiloValidities: $this->validities->forEquipmentSilos($alatBerat),
            equipmentHasActiveAssignment: $equipmentAssignment->exists(),
            operatorHasActiveAssignmentElsewhere: $operatorAssignmentElsewhere->exists(),
            today: $today,
        );
    }

    /**
     * @throws AssignmentIneligible
     */
    public function handle(AlatBerats $alatBerat, Operators $operator, CarbonInterface $tanggalMulai): OperatorAlatAssignment
    {
        $this->authorize('update', $alatBerat);
        $this->authorize('update', $operator);

        $assignment = DB::transaction(function () use ($alatBerat, $operator, $tanggalMulai) {
            $reasons = $this->checkEligibility($alatBerat, $operator, CarbonImmutable::now(), lockForUpdate: true);

            if ($reasons !== []) {
                throw new AssignmentIneligible($reasons);
            }

            return OperatorAlatAssignment::create([
                'alat_berat_id' => $alatBerat->id,
                'operator_id' => $operator->id,
                'tanggal_mulai' => $tanggalMulai,
                'is_active' => true,
            ]);
        });

        Log::info('assignment.created', [
            'assignment_id' => $assignment->getKey(),
            'alat_berat_id' => $alatBerat->id,
            'operator_id' => $operator->id,
            'tanggal_mulai' => $tanggalMulai->toDateString(),
            'user_id' => auth()->id(),
        ]);

        return $assignment;
    }
}
