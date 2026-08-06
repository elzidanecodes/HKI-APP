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
use Illuminate\Support\Facades\DB;

/**
 * Assigns an operator to a piece of equipment — replacing the four
 * guards currently duplicated inside
 * OperatorAssignmentsRelationManager's ->before() closure
 * (TECHNICAL_AUDIT.md §7) with a single call to AssignmentEligibility
 * (Milestone M2.3). Not yet wired into that RelationManager — the
 * strangler cutover happens in Milestone M2.5.
 *
 * Authorization is stubbed as always-allow: Identity/Policies don't exist
 * until Phase 3 (Milestone M3.3 replaces this with a real check). No row
 * locking yet (Phase 4 / M4.2) — the transaction here only gives
 * atomicity for this single write, not protection against a concurrent
 * request passing the same eligibility check first.
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
 */
final class AssignOperator
{
    public function __construct(
        private readonly AssignmentEligibility $eligibility,
        private readonly DocumentValidityMapper $validities,
    ) {}

    /**
     * @return string[]
     */
    public function checkEligibility(AlatBerats $alatBerat, Operators $operator, CarbonInterface $today): array
    {
        return $this->eligibility->ineligibilityReasons(
            operatorSioValidities: $this->validities->forOperatorSios($operator),
            equipmentSiloValidities: $this->validities->forEquipmentSilos($alatBerat),
            equipmentHasActiveAssignment: $alatBerat->activeAssignment()->exists(),
            operatorHasActiveAssignmentElsewhere: OperatorAlatAssignment::where('operator_id', $operator->id)
                ->where('is_active', true)
                ->exists(),
            today: $today,
        );
    }

    /**
     * @throws AssignmentIneligible
     */
    public function handle(AlatBerats $alatBerat, Operators $operator, CarbonInterface $tanggalMulai): OperatorAlatAssignment
    {
        $this->authorize();

        return DB::transaction(function () use ($alatBerat, $operator, $tanggalMulai) {
            $reasons = $this->checkEligibility($alatBerat, $operator, CarbonImmutable::now());

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
    }

    private function authorize(): void
    {
        // Stubbed: always allowed until Phase 3 (Identity/Policies) exists.
    }
}
