<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $schedule->command('assignment:auto-end-expired')->daily();
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
