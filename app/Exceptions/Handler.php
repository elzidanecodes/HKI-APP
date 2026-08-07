<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5: this
     * closure was empty — every exception, including domain rejections
     * like AssignOperator's AssignmentIneligible
     * (ARCHITECTURE_BLUEPRINT.md Diagram 2's "DomainException(alasan)"
     * path), reached it and left no trace. ARCHITECTURE_BLUEPRINT.md
     * §8.8: "Setiap exception domain — error". \DomainException is the
     * SPL base class AssignmentIneligible already extends, so this covers
     * any future domain exception the same way without per-exception
     * wiring. Does not return false, so Laravel's own default exception
     * reporting still runs afterward — this adds an explicit, structured
     * line, it does not replace the framework's.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            if ($e instanceof \DomainException) {
                Log::error('domain.exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'user_id' => auth()->id(),
                ]);
            }
        });
    }
}
