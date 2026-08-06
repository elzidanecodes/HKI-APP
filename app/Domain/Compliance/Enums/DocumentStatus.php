<?php

namespace App\Domain\Compliance\Enums;

/**
 * Derived, never stored. TECHNICAL_AUDIT.md finding C2: the sios/silos
 * `is_active` column exists in schema but is never written by any code
 * path, so it silently disagrees with what the UI and guards actually
 * compute from dates. This enum is the single source of truth instead —
 * always computed from a ValidityPeriod, never persisted as a column.
 */
enum DocumentStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
}
