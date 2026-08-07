<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Re-enabled per IMPLEMENTATION_PLAN.md Phase 2 / M2.5: the command
        // now delegates to App\Application\Operations\
        // AutoEndIneligibleAssignments, which asks the correct question
        // (¬∃ valid document, not ∃ expired) — TECHNICAL_AUDIT.md C1 is
        // closed. Disabled since Phase 0 / M0.1; see
        // docs/interim/M0.1-manual-assignment-review.md for the interim
        // manual process this replaces.
        //
        // IMPLEMENTATION_PLAN.md Milestone M4.4 / TECHNICAL_AUDIT.md H5:
        // this entry previously had neither guard. withoutOverlapping()
        // stops a slow run from double-processing the same assignments if
        // the previous night's run is still in flight; onFailure() is the
        // other half of H5's "runs unattended, fails silently" complaint —
        // a crashed run previously vanished with no trace. "Output
        // persisten" (ARCHITECTURE_BLUEPRINT.md §8.8) is satisfied by the
        // Log:: calls this milestone added inside AutoEndIneligibleAssignments
        // itself (the started/completed/auto_ended lines), not a second,
        // redundant ->appendOutputTo() capturing the same information from
        // stdout.
        $schedule->command('assignment:auto-end-expired')
            ->daily()
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('scheduler.auto_end_expired.failed');
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
