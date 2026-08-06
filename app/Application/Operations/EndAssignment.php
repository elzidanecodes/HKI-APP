<?php

namespace App\Application\Operations;

use App\Models\OperatorAlatAssignment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Manually ends an active assignment — the same write the "End" row
 * action already performs inside OperatorAssignmentsRelationManager
 * today, now owning its own transaction boundary and authorization
 * check (P5). Not yet wired into that RelationManager (Milestone M2.5).
 *
 * Authorization is stubbed as always-allow: Identity/Policies don't exist
 * until Phase 3 (Milestone M3.3 replaces this with a real check).
 */
final class EndAssignment
{
    public function handle(OperatorAlatAssignment $assignment, CarbonInterface $tanggalSelesai): OperatorAlatAssignment
    {
        $this->authorize();

        return DB::transaction(function () use ($assignment, $tanggalSelesai) {
            $assignment->update([
                'tanggal_selesai' => $tanggalSelesai,
                'is_active' => false,
            ]);

            return $assignment;
        });
    }

    private function authorize(): void
    {
        // Stubbed: always allowed until Phase 3 (Identity/Policies) exists.
    }
}
