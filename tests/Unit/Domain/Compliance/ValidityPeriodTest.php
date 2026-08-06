<?php

namespace Tests\Unit\Domain\Compliance;

use App\Domain\Compliance\ValueObjects\ValidityPeriod;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests — no database, no framework boot, matching
 * DocumentValidityTest.php's convention. IMPLEMENTATION_PLAN.md Milestone
 * M2.7: startingFrom() replaces the addYear() computation this milestone
 * removes from Silos::booted() (TECHNICAL_AUDIT.md M6).
 */
class ValidityPeriodTest extends TestCase
{
    public function test_starting_from_adds_the_given_number_of_months(): void
    {
        $issuedAt = CarbonImmutable::parse('2026-01-15');

        $period = ValidityPeriod::startingFrom($issuedAt, 12);

        $this->assertTrue($issuedAt->isSameDay($period->startsAt));
        $this->assertTrue($period->expiresAt->isSameDay(CarbonImmutable::parse('2027-01-15')));
    }

    public function test_starting_from_normalizes_the_expiry_to_start_of_day(): void
    {
        $issuedAt = CarbonImmutable::parse('2026-01-15 14:30:00');

        $period = ValidityPeriod::startingFrom($issuedAt, 1);

        $this->assertTrue($period->expiresAt->isStartOfDay());
    }

    public function test_starting_from_supports_a_validity_length_other_than_twelve_months(): void
    {
        $issuedAt = CarbonImmutable::parse('2026-01-01');

        $period = ValidityPeriod::startingFrom($issuedAt, 6);

        $this->assertTrue($period->expiresAt->isSameDay(CarbonImmutable::parse('2026-07-01')));
    }
}
