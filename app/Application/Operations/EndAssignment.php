<?php

namespace App\Application\Operations;

use App\Models\OperatorAlatAssignment;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

/**
 * Manually ends an active assignment — the same write the "End" row
 * action already performs inside OperatorAssignmentsRelationManager
 * today (Milestone M2.5 wired that in — this class's own earlier
 * "not yet wired in" note no longer applies), now owning its own
 * transaction boundary and authorization check (P5).
 *
 * Authorization is enforced against both entities involved
 * (IMPLEMENTATION_PLAN.md Milestone M3.3), same reasoning as
 * AssignOperator: ending an assignment touches both the equipment and
 * the operator, so LOGISTIK's "no access to Operators" exclusion must
 * hold here too.
 */
final class EndAssignment
{
    use AuthorizesRequests;

    public function handle(OperatorAlatAssignment $assignment, CarbonInterface $tanggalSelesai): OperatorAlatAssignment
    {
        $this->authorize('update', $assignment->alatBerat()->firstOrFail());
        $this->authorize('update', $assignment->operator()->firstOrFail());

        return DB::transaction(function () use ($assignment, $tanggalSelesai) {
            $assignment->update([
                'tanggal_selesai' => $tanggalSelesai,
                'is_active' => false,
            ]);

            return $assignment;
        });
    }
}
