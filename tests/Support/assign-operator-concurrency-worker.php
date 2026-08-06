<?php

/**
 * Standalone worker for IMPLEMENTATION_PLAN.md Milestone M4.2's
 * concurrency test (AssignOperatorConcurrencyTest). Not a production
 * script — SQLite's :memory: connection is per-process, and PHPUnit is
 * single-threaded, so genuinely testing "two near-simultaneous requests"
 * through the real AssignOperator::handle() code requires two real OS
 * processes sharing one on-disk SQLite file. This is that second
 * process: it takes an alat_berat/operator/user id, logs in as that
 * user, and calls AssignOperator::handle() exactly as a real request
 * would, then reports success/failure via exit code.
 *
 * Usage: php assign-operator-concurrency-worker.php <alatBeratId> <operatorId> <userId>
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

[$alatBeratId, $operatorId, $userId] = array_slice($argv, 1);

try {
    Illuminate\Support\Facades\Auth::loginUsingId((int) $userId);

    $alatBerat = App\Models\AlatBerats::findOrFail((int) $alatBeratId);
    $operator = App\Models\Operators::findOrFail((int) $operatorId);

    $action = new App\Application\Operations\AssignOperator(
        new App\Domain\Operations\Services\AssignmentEligibility,
        new App\Application\Compliance\DocumentValidityMapper,
    );

    $assignment = $action->handle($alatBerat, $operator, Carbon\CarbonImmutable::now());

    fwrite(STDOUT, "SUCCESS assignment_id={$assignment->id}\n");
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, get_class($e).': '.$e->getMessage()."\n");
    exit(1);
}
