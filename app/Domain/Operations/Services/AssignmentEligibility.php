<?php

namespace App\Domain\Operations\Services;

use App\Domain\Compliance\ValueObjects\DocumentValidity;
use App\Domain\Operations\Enums\AssignmentStatus;
use Carbon\CarbonInterface;

/**
 * Single source of truth for "can this operator be assigned to this
 * equipment?" — replacing the four independent guards currently spread
 * across OperatorAssignmentsRelationManager's ->disabled() and ->before()
 * closures (TECHNICAL_AUDIT.md §7, "Business Logic — Terfragmentasi").
 *
 * Every validity question is delegated to DocumentValidity::anyValid(),
 * so both the SIO and SILO checks use the same correct quantifier
 * ("does at least one valid document exist?") that fixes C1 — not the
 * scheduler's inverted "does an expired one exist?".
 */
final class AssignmentEligibility
{
    /**
     * @param  DocumentValidity[]  $operatorSioValidities
     * @param  DocumentValidity[]  $equipmentSiloValidities
     * @return string[] Reasons the assignment is ineligible; empty means eligible.
     */
    public function ineligibilityReasons(
        array $operatorSioValidities,
        array $equipmentSiloValidities,
        bool $equipmentHasActiveAssignment,
        bool $operatorHasActiveAssignmentElsewhere,
        CarbonInterface $today,
    ): array {
        $reasons = [];

        if (! DocumentValidity::anyValid($equipmentSiloValidities, $today)) {
            $reasons[] = 'equipment_silo_not_active';
        }

        if ($equipmentHasActiveAssignment) {
            $reasons[] = 'equipment_already_assigned';
        }

        if ($operatorHasActiveAssignmentElsewhere) {
            $reasons[] = 'operator_already_assigned_elsewhere';
        }

        if (! DocumentValidity::anyValid($operatorSioValidities, $today)) {
            $reasons[] = 'operator_sio_not_active';
        }

        return $reasons;
    }

    public function isEligible(
        array $operatorSioValidities,
        array $equipmentSiloValidities,
        bool $equipmentHasActiveAssignment,
        bool $operatorHasActiveAssignmentElsewhere,
        CarbonInterface $today,
    ): bool {
        return $this->ineligibilityReasons(
            $operatorSioValidities,
            $equipmentSiloValidities,
            $equipmentHasActiveAssignment,
            $operatorHasActiveAssignmentElsewhere,
            $today,
        ) === [];
    }

    public function status(
        array $operatorSioValidities,
        array $equipmentSiloValidities,
        bool $equipmentHasActiveAssignment,
        bool $operatorHasActiveAssignmentElsewhere,
        CarbonInterface $today,
    ): AssignmentStatus {
        return $this->isEligible(
            $operatorSioValidities,
            $equipmentSiloValidities,
            $equipmentHasActiveAssignment,
            $operatorHasActiveAssignmentElsewhere,
            $today,
        ) ? AssignmentStatus::Active : AssignmentStatus::Rejected;
    }
}
