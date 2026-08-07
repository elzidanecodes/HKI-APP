<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5: the
 * nightly assignment:auto-end-expired entry had no ->withoutOverlapping()
 * (a slow run could double-process the same rows) and no ->onFailure()
 * (a crashed run vanished silently — H5's core complaint). Both are wired
 * in App\Console\Kernel::schedule().
 *
 * These drive the real Illuminate\Console\Scheduling\Event object
 * Kernel::schedule() produces (resolved via the container, same as the
 * scheduler itself does), not a reimplementation of it — the same
 * "prove the observable behavior through the real code" standard
 * Milestone M4.2's concurrency test used for its own scheduler-adjacent
 * claim.
 */
class SchedulerObservabilityTest extends TestCase
{
    private function assignmentEvent(): Event
    {
        $schedule = $this->app->make(Schedule::class);

        foreach ($schedule->events() as $event) {
            if (str_contains((string) $event->command, 'assignment:auto-end-expired')) {
                return $event;
            }
        }

        $this->fail('assignment:auto-end-expired is not registered on the schedule.');
    }

    public function test_a_second_concurrent_run_is_skipped_while_one_is_in_progress(): void
    {
        $event = $this->assignmentEvent();

        $this->assertTrue(
            $event->withoutOverlapping,
            'Expected the scheduled event to have withoutOverlapping() enabled.'
        );
        $this->assertFalse(
            $event->shouldSkipDueToOverlapping(),
            'A fresh event should not be skipped before anything has claimed its mutex.'
        );

        // Simulate a first run already in progress by claiming its mutex
        // directly, the same call Event::run() makes internally.
        $event->mutex->create($event);

        $this->assertTrue(
            $event->shouldSkipDueToOverlapping(),
            'A second, concurrent run should be skipped while the mutex is held.'
        );

        $event->mutex->forget($event);

        $this->assertFalse(
            $event->shouldSkipDueToOverlapping(),
            'Releasing the mutex should allow the next scheduled run through.'
        );
    }

    public function test_a_failed_run_logs_an_error(): void
    {
        Log::spy();

        $event = $this->assignmentEvent();

        // Event::finish() is what Illuminate\Console\Scheduling\Schedule
        // calls after a real run completes; calling it directly with a
        // non-zero exit code exercises the exact onFailure() callback
        // Kernel::schedule() registered, without shelling out a real
        // `php artisan` process.
        $event->finish($this->app, 1);

        Log::shouldHaveReceived('error')->withArgs(
            fn (string $message) => $message === 'scheduler.auto_end_expired.failed'
        )->atLeast()->once();
    }

    public function test_a_successful_run_does_not_log_a_failure(): void
    {
        Log::spy();

        $event = $this->assignmentEvent();

        $event->finish($this->app, 0);

        Log::shouldNotHaveReceived('error');
    }
}
