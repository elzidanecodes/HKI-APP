<?php

namespace App\Application\Compliance;

use App\Models\Silos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Renews a legal document (SIO or SILO) by issuing a new one for the
 * same operator or equipment — the schema has no predecessor/renewal
 * link, so mechanically this is the same operation as IssueDocument,
 * kept as a separate Action because it represents a distinct business
 * moment (renewal vs. first issuance), matching
 * ARCHITECTURE_BLUEPRINT.md §4 Diagram 3.
 *
 * TECHNICAL_AUDIT.md H2: renewing a SILO before the existing one expires
 * is now allowed — Milestone M2.7 removed the persistence-level guard
 * that used to block it (Silos::booted()), so early renewal simply
 * results in two overlapping valid SILO periods for the same equipment,
 * matching ARCHITECTURE_BLUEPRINT.md §4 Diagram 3's state machine.
 *
 * Authorization is enforced via the registered SiloPolicy/SioPolicy
 * (IMPLEMENTATION_PLAN.md Milestone M3.3), authorized as 'create' since
 * renewal mechanically creates a new document row (see class comment
 * above) — there is no distinct 'renew' Policy method.
 *
 * Logs at `info` after commit, not inside the transaction closure
 * (ARCHITECTURE_BLUEPRINT.md §8.4) — IMPLEMENTATION_PLAN.md Milestone
 * M4.4, closing TECHNICAL_AUDIT.md H5 for the Compliance module.
 */
final class RenewDocument
{
    use AuthorizesRequests;

    public function handle(Model $document): Model
    {
        $this->authorize('create', get_class($document));

        $document = DB::transaction(function () use ($document) {
            $this->applyDefaultExpiry($document);

            $document->save();

            return $document;
        });

        Log::info('document.renewed', [
            'document_type' => get_class($document),
            'document_id' => $document->getKey(),
            'attributes' => $document->only($document->getFillable()),
            'user_id' => auth()->id(),
        ]);

        return $document;
    }

    /**
     * Silos::booted() previously computed this automatically
     * (TECHNICAL_AUDIT.md M6); moved here per IMPLEMENTATION_PLAN.md
     * Milestone M2.7. Sios has no equivalent — its expiry is entered
     * directly (config/hse.php, TECHNICAL_AUDIT.md M9).
     */
    private function applyDefaultExpiry(Model $document): void
    {
        if ($document instanceof Silos && $document->getAttribute('tanggal_terbit') !== null) {
            $document->tanggal_expired = SiloExpiryCalculator::expiresAt($document->tanggal_terbit);
        }
    }
}
