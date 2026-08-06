<?php

namespace App\Application\Compliance;

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
            $document->save();

            return $document;
        });
    }

    private function authorize(): void
    {
        // Stubbed: always allowed until Phase 3 (Identity/Policies) exists.
    }
}
