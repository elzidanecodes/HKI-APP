<?php

namespace App\Domain\Compliance\Enums;

/**
 * Derived, never stored. TECHNICAL_AUDIT.md finding C2: the sios/silos
 * `is_active` column existed in schema but was never written by any code
 * path, so it silently disagreed with what the UI and guards actually
 * computed from dates. This enum is the single source of truth instead —
 * always computed from a ValidityPeriod, never persisted as a column.
 * The dead column itself was dropped in M2.6 (expand→migrate→contract).
 */
enum DocumentStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
}
