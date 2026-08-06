<?php

namespace App\Application\Compliance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Renews a legal document (SIO or SILO) by issuing a new one for the
 * same operator or equipment — the schema has no predecessor/renewal
 * link, so mechanically this is the same operation as IssueDocument,
 * kept as a separate Action because it represents a distinct business
 * moment (renewal vs. first issuance), matching
 * ARCHITECTURE_BLUEPRINT.md §4 Diagram 3.
 *
 * TECHNICAL_AUDIT.md H2: renewing a SILO before the existing one expires
 * should be allowed. This Action does not itself enforce any such
 * restriction — but Silos::booted() still blocks it at the persistence
 * layer today; that hook is only removed in Milestone M2.7. Until then,
 * an early SILO renewal through this Action still fails, characterized
 * by RenewDocumentTest::test_h2_early_silo_renewal_still_blocked_today().
 *
 * Authorization is stubbed as always-allow: Identity/Policies don't exist
 * until Phase 3 (Milestone M3.3 replaces this with a real check).
 */
final class RenewDocument
{
    public function handle(Model $document): Model
    {
        $this->authorize();

        return DB::transaction(function () use ($document) {
            $document->save();

            return $document;
        });
    }

    private function authorize(): void
    {
        // Stubbed: always allowed until Phase 3 (Identity/Policies) exists.
    }
}
