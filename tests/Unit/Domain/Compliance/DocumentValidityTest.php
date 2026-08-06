<?php

namespace Tests\Unit\Domain\Compliance;

use App\Domain\Compliance\Enums\DocumentStatus;
use App\Domain\Compliance\ValueObjects\DocumentValidity;
use App\Domain\Compliance\ValueObjects\ValidityPeriod;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests — no database, no framework boot (this class extends
 * plain PHPUnit\Framework\TestCase, matching tests/Unit/ExampleTest.php's
 * existing convention, not Tests\TestCase). IMPLEMENTATION_PLAN.md
 * Milestone M2.2 completion criteria: zero DB access.
 */
class DocumentValidityTest extends TestCase
{
    private const THRESHOLD_DAYS = 30;

    private function validity(CarbonImmutable $expiresAt, ?CarbonImmutable $startsAt = null): DocumentValidity
    {
        return DocumentValidity::forPeriod(
            ValidityPeriod::fromDates($startsAt ?? $expiresAt->subYear(), $expiresAt),
            self::THRESHOLD_DAYS,
        );
    }

    public function test_document_is_valid_and_active_when_far_from_expiry(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $validity = $this->validity($today->addMonths(6));

        $this->assertTrue($validity->isValidOn($today));
        $this->assertSame(DocumentStatus::Active, $validity->status($today));
    }

    public function test_document_is_expired_the_day_after_its_expiry_date(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $validity = $this->validity($today->subDay());

        $this->assertFalse($validity->isValidOn($today));
        $this->assertSame(DocumentStatus::Expired, $validity->status($today));
    }

    public function test_document_is_still_valid_on_its_exact_expiry_date(): void
    {
        // Matches every existing implementation this replaces: >= today,
        // not > today.
        $today = CarbonImmutable::parse('2026-06-01');
        $validity = $this->validity($today);

        $this->assertTrue($validity->isValidOn($today));
        $this->assertNotSame(DocumentStatus::Expired, $validity->status($today));
    }

    public function test_document_is_expiring_soon_within_the_threshold(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $validity = $this->validity($today->addDays(10));

        $this->assertTrue($validity->isValidOn($today));
        $this->assertSame(DocumentStatus::ExpiringSoon, $validity->status($today));
    }

    public function test_document_is_expiring_soon_at_the_exact_threshold_boundary(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $validity = $this->validity($today->addDays(self::THRESHOLD_DAYS));

        $this->assertSame(DocumentStatus::ExpiringSoon, $validity->status($today));
    }

    public function test_document_is_active_one_day_beyond_the_threshold_boundary(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');
        $validity = $this->validity($today->addDays(self::THRESHOLD_DAYS + 1));

        $this->assertSame(DocumentStatus::Active, $validity->status($today));
    }

    public function test_remaining_days_reflects_a_signed_day_count(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');

        $this->assertSame(10, $this->validity($today->addDays(10))->remainingDaysOn($today));
        $this->assertSame(-5, $this->validity($today->subDays(5))->remainingDaysOn($today));
    }

    // --- C1: the correct quantifier ("¬∃ valid", not "∃ expired") -------

    public function test_any_valid_is_true_when_at_least_one_document_is_valid(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');

        $this->assertTrue(DocumentValidity::anyValid([
            $this->validity($today->subMonths(6)), // expired
            $this->validity($today->addMonths(6)), // valid
        ], $today));
    }

    public function test_any_valid_is_false_when_every_document_is_expired(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');

        $this->assertFalse(DocumentValidity::anyValid([
            $this->validity($today->subMonths(6)),
            $this->validity($today->subDay()),
        ], $today));
    }

    public function test_any_valid_is_false_with_no_documents_at_all(): void
    {
        $this->assertFalse(DocumentValidity::anyValid([], CarbonImmutable::parse('2026-06-01')));
    }

    // CHARACTERIZES what C1 got wrong, as a direct contrast. The scheduler
    // asked "does an EXPIRED document exist?" — which is true here, even
    // though the operator/equipment is legitimately compliant. anyValid()
    // asks the correct question and must return true for this exact case:
    // one old expired document kept for audit history, one currently
    // valid renewal.
    public function test_renewal_scenario_that_triggers_c1_is_correctly_resolved(): void
    {
        $today = CarbonImmutable::parse('2026-06-01');

        $oldExpired = $this->validity($today->subMonths(6));
        $renewedValid = $this->validity($today->addMonths(6));

        // The bug's own question — "does an expired one exist?" — is true,
        // and answering it directly is exactly the mistake C1 made.
        $this->assertTrue($oldExpired->status($today) === DocumentStatus::Expired);

        // The correct question is answered here, and must be true.
        $this->assertTrue(DocumentValidity::anyValid([$oldExpired, $renewedValid], $today));
    }
}
