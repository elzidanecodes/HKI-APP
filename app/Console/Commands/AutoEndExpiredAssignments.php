<?php

namespace App\Console\Commands;

use App\Application\Operations\AutoEndIneligibleAssignments;
use Illuminate\Console\Command;

/**
 * Thin wrapper around App\Application\Operations\AutoEndIneligibleAssignments
 * (IMPLEMENTATION_PLAN.md Milestone M2.4/M2.5). The command used to
 * implement this check itself, asking "does an EXPIRED document exist?"
 * instead of "does NO VALID document exist?" — TECHNICAL_AUDIT.md
 * finding C1. That logic now lives in one place, tested independently
 * of the console layer.
 */
class AutoEndExpiredAssignments extends Command
{
    protected $signature = 'assignment:auto-end-expired';

    protected $description = 'Auto end assignment jika SIO atau SILO tidak lagi valid';

    public function handle(AutoEndIneligibleAssignments $action)
    {
        $ended = $action->handle(now());

        $this->info("{$ended} assignment(s) di-end otomatis");

        return Command::SUCCESS;
    }
}
