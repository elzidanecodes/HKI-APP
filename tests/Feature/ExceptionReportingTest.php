<?php

namespace Tests\Feature;

use App\Domain\Operations\Exceptions\AssignmentIneligible;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5:
 * app/Exceptions/Handler.php::reportable() was an empty closure — a
 * domain exception like AssignmentIneligible (thrown by AssignOperator;
 * ARCHITECTURE_BLUEPRINT.md Diagram 2's "DomainException(alasan)" path)
 * reached it and left no trace. ARCHITECTURE_BLUEPRINT.md §8.8: "Setiap
 * exception domain — error".
 */
class ExceptionReportingTest extends TestCase
{
    public function test_a_domain_exception_is_logged_at_error_level(): void
    {
        Log::spy();

        $exception = new AssignmentIneligible(['operator_sio_not_active']);

        $this->app->make(ExceptionHandler::class)->report($exception);

        Log::shouldHaveReceived('error')->withArgs(
            function (string $message, array $context) {
                return $message === 'domain.exception'
                    && $context['exception'] === AssignmentIneligible::class
                    && str_contains($context['message'], 'operator_sio_not_active');
            }
        )->atLeast()->once();
    }

    // A framework exception (not a business-rule rejection) should not be
    // misreported as a domain exception — only \DomainException subtypes
    // get this treatment.
    public function test_a_non_domain_exception_is_not_logged_as_a_domain_exception(): void
    {
        Log::spy();

        $this->app->make(ExceptionHandler::class)->report(new \RuntimeException('unrelated failure'));

        Log::shouldNotHaveReceived('error', ['domain.exception', Mockery::any()]);
    }
}
