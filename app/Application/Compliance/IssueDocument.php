<?php

namespace App\Application\Compliance;

use App\Models\Silos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

/**
 * Issues the first legal document (SIO or SILO) for an operator or piece
 * of equipment. Generic over the document model — the caller constructs
 * an unsaved Sios or Silos instance; this Action owns the transaction
 * boundary and authorization check around persisting it (P5).
 *
 * Authorization is enforced via the registered SiloPolicy/SioPolicy
 * (IMPLEMENTATION_PLAN.md Milestone M3.3) — this is the
 * "PENEGAKAN SEBENARNYA" point per ARCHITECTURE_BLUEPRINT.md Diagram 2,
 * not the UI.
 */
final class IssueDocument
{
    use AuthorizesRequests;

    public function handle(Model $document): Model
    {
        $this->authorize('create', get_class($document));

        return DB::transaction(function () use ($document) {
            $this->applyDefaultExpiry($document);

            $document->save();

            return $document;
        });
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
