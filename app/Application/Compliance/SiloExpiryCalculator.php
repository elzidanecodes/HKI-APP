<?php

namespace App\Application\Compliance;

use App\Domain\Compliance\ValueObjects\ValidityPeriod;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Resolves config('hse.silo_validity_months') and calls
 * ValidityPeriod::startingFrom() — the Application-layer glue Domain
 * cannot contain itself (Blueprint P4: no config() inside app/Domain/).
 * Shared by IssueDocument, RenewDocument, and EditSilos so the one
 * remaining call site of TECHNICAL_AUDIT.md M6's rule isn't triplicated
 * (IMPLEMENTATION_PLAN.md Milestone M2.7 — this replaces
 * Silos::booted()'s addYear() computation).
 *
 * Returns a mutable Carbon, not the Domain's CarbonImmutable, because
 * every caller assigns the result directly to Silos::$tanggal_expired
 * (cast 'date', i.e. Carbon) — this is the one place that boundary
 * conversion happens.
 */
final class SiloExpiryCalculator
{
    public static function expiresAt(CarbonInterface $tanggalTerbit): Carbon
    {
        return Carbon::instance(
            ValidityPeriod::startingFrom(
                $tanggalTerbit,
                config('hse.silo_validity_months'),
            )->expiresAt
        );
    }
}
