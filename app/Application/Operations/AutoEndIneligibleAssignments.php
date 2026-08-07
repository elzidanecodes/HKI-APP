<?php

namespace App\Application\Operations;

use App\Application\Compliance\DocumentValidityMapper;
use App\Domain\Compliance\ValueObjects\DocumentValidity;
use App\Models\OperatorAlatAssignment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Ends every active assignment whose operator has no currently valid SIO
 * or whose equipment has no currently valid SILO. This is the fix for
 * TECHNICAL_AUDIT.md C1: the current scheduler
 * (AutoEndExpiredAssignments) asks "does an EXPIRED document exist?"
 * instead of "does NO VALID document exist?" — since expired documents
 * are kept for audit history, that inverted question is true for anyone
 * who has ever renewed, ending a legitimate assignment. This Action asks
 * the correct question via DocumentValidity::anyValid() (¬∃ valid,
 * negated), the same primitive AssignmentEligibility (Milestone M2.3)
 * uses.
 *
 * Deliberately does NOT go through AssignmentEligibility: that service
 * also checks "is the equipment/operator already assigned elsewhere" —
 * trivially true here, since the assignment being re-checked is itself
 * what makes them "assigned". Re-evaluating whether an assignment should
 * *continue* is a different question from whether a *new* one may be
 * *created*, even though both share the same document-validity check.
 *
 * Wired into the `assignment:auto-end-expired` console command
 * (Milestone M2.5), which the scheduler runs unattended with no
 * authenticated user. Deliberately has no $this->authorize() call
 * (IMPLEMENTATION_PLAN.md Milestone M3.3): Policies answer "can this
 * USER do X," which has no meaning for a cron-triggered system process —
 * adding one would make the nightly run fail with no user to check
 * against, silently reintroducing the C1-class outage this Action exists
 * to prevent. AssignOperator and EndAssignment, by contrast, always run
 * inside an authenticated Filament session, so they do authorize.
 *
 * One Action = one transaction (Blueprint §8.4): the whole batch runs in
 * a single transaction, matching the current command's implicit
 * behavior of one run producing one consistent set of changes. Row
 * locking is deferred to Phase 4 / M4.2.
 *
 * Milestone M4.4 (TECHNICAL_AUDIT.md H5): this is the unattended
 * automation the audit singled out — "sistem merusak data secara diam-diam,
 * setiap malam, tanpa jejak apapun." ARCHITECTURE_BLUEPRINT.md §8.8 maps
 * this Action to three log lines: `info` when the sweep starts (how many
 * active assignments will be evaluated), `notice` per assignment actually
 * ended (with the specific reason — SIO, SILO, or both), and `info` when
 * the sweep completes (processed vs. ended counts). Logging happens
 * inside the transaction here, unlike the other Actions: this is one
 * batch operation over N rows, and the per-row `notice` is itself part of
 * what "jumlah terubah" means at completion, not a side effect to defer —
 * there is no meaningful "commit, then log" split for a summary of the
 * work the transaction just did.
 */
final class AutoEndIneligibleAssignments
{
    public function __construct(private readonly DocumentValidityMapper $validities) {}

    /**
     * @return int Number of assignments ended.
     */
    public function handle(CarbonInterface $today): int
    {
        return DB::transaction(function () use ($today) {
            $activeAssignments = OperatorAlatAssignment::where('is_active', true)->get();

            Log::info('scheduler.auto_end_ineligible_assignments.started', [
                'active_assignment_count' => $activeAssignments->count(),
                'today' => $today->toDateString(),
            ]);

            $ended = 0;

            $activeAssignments->each(function (OperatorAlatAssignment $assignment) use ($today, &$ended) {
                $operatorSioValid = DocumentValidity::anyValid($this->validities->forOperatorSios($assignment->operator()->firstOrFail()), $today);
                $equipmentSiloValid = DocumentValidity::anyValid($this->validities->forEquipmentSilos($assignment->alatBerat()->firstOrFail()), $today);

                if ($operatorSioValid && $equipmentSiloValid) {
                    return;
                }

                $assignment->update([
                    'tanggal_selesai' => $today,
                    'is_active' => false,
                ]);

                $reasons = array_filter([
                    $operatorSioValid ? null : 'operator has no currently valid SIO',
                    $equipmentSiloValid ? null : 'equipment has no currently valid SILO',
                ]);

                Log::notice('assignment.auto_ended', [
                    'assignment_id' => $assignment->getKey(),
                    'alat_berat_id' => $assignment->alat_berat_id,
                    'operator_id' => $assignment->operator_id,
                    'reasons' => array_values($reasons),
                ]);

                $ended++;
            });

            Log::info('scheduler.auto_end_ineligible_assignments.completed', [
                'processed' => $activeAssignments->count(),
                'ended' => $ended,
            ]);

            return $ended;
        });
    }
}
