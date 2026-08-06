<?php

namespace App\Application\Compliance;

use App\Domain\Compliance\ValueObjects\DocumentValidity;
use App\Domain\Compliance\ValueObjects\ValidityPeriod;
use App\Models\AlatBerats;
use App\Models\Operators;
use Carbon\CarbonImmutable;

/**
 * Bridges Eloquent (Sios/Silos) into DocumentValidity, the Domain value
 * object from Milestone M2.2. This bridging must live in Application, not
 * Domain, since Domain may not depend on App\Models — but it's needed by
 * both AssignOperator and AutoEndIneligibleAssignments (Milestone M2.4),
 * so it's extracted here rather than duplicated across both.
 *
 * Sios has no tanggal_terbit column (TECHNICAL_AUDIT.md M9) — created_at
 * stands in as the period start, which is inert data ValidityPeriod never
 * uses to gate validity (only the expiry bound does).
 */
final class DocumentValidityMapper
{
    /**
     * @return DocumentValidity[]
     */
    public function forOperatorSios(Operators $operator): array
    {
        return $operator->sio()->get()
            ->map(fn ($sio) => DocumentValidity::forPeriod(
                ValidityPeriod::fromDates(
                    CarbonImmutable::instance($sio->created_at),
                    CarbonImmutable::parse($sio->tanggal_expired),
                ),
                config('hse.expiring_soon_threshold_days'),
            ))
            ->all();
    }

    /**
     * @return DocumentValidity[]
     */
    public function forEquipmentSilos(AlatBerats $alatBerat): array
    {
        return $alatBerat->silos()->get()
            ->map(fn ($silo) => DocumentValidity::forPeriod(
                ValidityPeriod::fromDates(
                    CarbonImmutable::parse($silo->tanggal_terbit),
                    CarbonImmutable::parse($silo->tanggal_expired),
                ),
                config('hse.expiring_soon_threshold_days'),
            ))
            ->all();
    }
}
