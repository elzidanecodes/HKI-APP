<?php

namespace App\Domain\Compliance\Contracts;

use App\Domain\Compliance\ValueObjects\DocumentValidity;

/**
 * Implemented by both SIO and SILO once Milestones M2.5/M2.7 cut them
 * over — the shared contract that closes TECHNICAL_AUDIT.md finding M9
 * (SIO and SILO currently follow different, hand-rolled validity rules
 * with no common shape). Not yet implemented by any Eloquent model as of
 * this milestone; adding new document types later means implementing
 * this contract, not copying the SIO/SILO pattern.
 */
interface LegalDocument
{
    public function validity(): DocumentValidity;
}
