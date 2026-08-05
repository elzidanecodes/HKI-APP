<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Disabled per IMPLEMENTATION_PLAN.md Phase 0 / M0.1 (TECHNICAL_AUDIT.md C1):
        // this command's expiry check uses an inverted quantifier and ends valid
        // assignments after every document renewal. Re-enable only after Phase 2
        // (Domain Core) replaces its logic. See docs/interim/M0.1-manual-assignment-review.md
        // for the manual process to follow while this is disabled.
        // $schedule->command('assignment:auto-end-expired')->daily();
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
