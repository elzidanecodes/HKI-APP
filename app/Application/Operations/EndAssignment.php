<?php

namespace App\Application\Operations;

use App\Models\OperatorAlatAssignment;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
 *
 * Milestone M4.2: unlike AssignOperator, there's no separate
 * eligibility check here to lock — handle() ends the one specific
 * $assignment it's given, unconditionally. The row itself is
 * re-fetched with lockForUpdate() inside the transaction before being
 * updated, so a concurrent AssignOperator request checking this same
 * equipment/operator's active-assignment state (also lockForUpdate(),
 * same milestone) serializes against it rather than racing.
 *
 * Logs at `info` after commit, not inside the transaction closure
 * (ARCHITECTURE_BLUEPRINT.md §8.4) — IMPLEMENTATION_PLAN.md Milestone
 * M4.4, closing TECHNICAL_AUDIT.md H5 for Operations. Tagged
 * `reason: manual` to distinguish this from AutoEndIneligibleAssignments'
 * `assignment.auto_ended` notice, matching the manual-vs-automatic
 * distinction ARCHITECTURE_BLUEPRINT.md §4 Diagram 3 already draws
 * between "Selesai" and "DiakhiriOtomatis".
 */
final class EndAssignment
{
    use AuthorizesRequests;

    public function handle(OperatorAlatAssignment $assignment, CarbonInterface $tanggalSelesai): OperatorAlatAssignment
    {
        $this->authorize('update', $assignment->alatBerat()->firstOrFail());
        $this->authorize('update', $assignment->operator()->firstOrFail());

        $locked = DB::transaction(function () use ($assignment, $tanggalSelesai) {
            $locked = OperatorAlatAssignment::whereKey($assignment->getKey())->lockForUpdate()->firstOrFail();

            $locked->update([
                'tanggal_selesai' => $tanggalSelesai,
                'is_active' => false,
            ]);

            return $locked;
        });

        Log::info('assignment.ended', [
            'assignment_id' => $locked->getKey(),
            'alat_berat_id' => $locked->alat_berat_id,
            'operator_id' => $locked->operator_id,
            'tanggal_selesai' => $tanggalSelesai->toDateString(),
            'user_id' => auth()->id(),
            'reason' => 'manual',
        ]);

        return $locked;
    }
}
