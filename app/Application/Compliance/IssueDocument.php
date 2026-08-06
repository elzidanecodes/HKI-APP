<?php

namespace App\Application\Compliance;

use App\Models\Silos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Issues the first legal document (SIO or SILO) for an operator or piece
 * of equipment. Generic over the document model — the caller constructs
 * an unsaved Sios or Silos instance; this Action owns the transaction
 * boundary and authorization check around persisting it (P5).
 *
 * Authorization is stubbed as always-allow: Identity/Policies don't exist
 * until Phase 3 (Milestone M3.3 replaces this with a real check).
 */
final class IssueDocument
{
    public function handle(Model $document): Model
    {
        $this->authorize();

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

    private function authorize(): void
    {
        // Stubbed: always allowed until Phase 3 (Identity/Policies) exists.
    }
}
