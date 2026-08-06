<?php

namespace Tests\Feature\Application\Operations;

use App\Models\AlatBerats;
use App\Models\OperatorAlatAssignment;
use App\Models\Operators;
use App\Models\Silos;
use App\Models\Sios;
use App\Models\User;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.2's own testing requirement:
 * "concurrency test firing two near-simultaneous assignment requests for
 * the same equipment and asserting exactly one succeeds." TECHNICAL_AUDIT.md
 * H1: the invariant was only PHP-checked, never DB-guaranteed.
 *
 * SQLite's :memory: connection is per-process and PHPUnit runs
 * single-threaded, so this cannot be tested by calling
 * AssignOperator::handle() twice in sequence — that proves nothing about
 * a race. This spawns two real, independent OS processes
 * (tests/Support/assign-operator-concurrency-worker.php) sharing one
 * on-disk SQLite file, starts them concurrently, and asserts exactly one
 * wins — genuine concurrency through the real Action code, not a
 * simulation.
 *
 * Does not use RefreshDatabase / the suite's shared :memory: connection:
 * a dedicated on-disk file is required for two OS processes to
 * contend for the same lock.
 *
 * Honest caveat, checked manually while writing this test: on SQLite,
 * this passes even with the constraint migration rolled back, because
 * SQLite serializes ALL writers at the file level regardless — it has
 * no per-row concurrency to lose. That is not true of the production
 * MySQL connection, where two writers to *different* rows never
 * contend at all; there, the unique index this migration adds is what
 * actually makes the second write fail, not incidental engine
 * behavior. That mechanism is verified directly (not through this
 * OS-process test) in the migration's own manual verification — see
 * this milestone's summary. This test still earns its place: it proves
 * the *observable* behavior the milestone asks for end-to-end through
 * the real Action, which is what "two near-simultaneous requests,
 * exactly one succeeds" means to a caller.
 */
class AssignOperatorConcurrencyTest extends TestCase
{
    private string $dbPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbPath = tempnam(sys_get_temp_dir(), 'm42_concurrency_');

        config(['database.connections.m42_concurrency' => [
            'driver' => 'sqlite',
            'database' => $this->dbPath,
            'foreign_key_constraints' => true,
        ]]);
    }

    protected function tearDown(): void
    {
        @unlink($this->dbPath);

        parent::tearDown();
    }

    public function test_two_near_simultaneous_assignment_requests_for_the_same_equipment_result_in_exactly_one_success(): void
    {
        [$alatBeratId, $operatorXId, $operatorYId, $userId] = $this->seedSharedDatabase();

        $env = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $this->dbPath,
        ];
        $worker = base_path('tests/Support/assign-operator-concurrency-worker.php');

        $requestX = new Process(['php', $worker, $alatBeratId, $operatorXId, $userId], base_path(), $env);
        $requestY = new Process(['php', $worker, $alatBeratId, $operatorYId, $userId], base_path(), $env);

        // Both must start before either is waited on — this is what
        // makes the two requests actually overlap in wall-clock time.
        $requestX->start();
        $requestY->start();

        $requestX->wait();
        $requestY->wait();

        $successCount = (int) $requestX->isSuccessful() + (int) $requestY->isSuccessful();

        $this->assertSame(1, $successCount, sprintf(
            "Expected exactly one of the two concurrent requests to succeed.\nRequest X (exit %d): %s%s\nRequest Y (exit %d): %s%s",
            $requestX->getExitCode(), $requestX->getOutput(), $requestX->getErrorOutput(),
            $requestY->getExitCode(), $requestY->getOutput(), $requestY->getErrorOutput(),
        ));

        $activeAssignments = OperatorAlatAssignment::on('m42_concurrency')
            ->where('alat_berat_id', $alatBeratId)
            ->where('is_active', true)
            ->count();

        $this->assertSame(1, $activeAssignments);
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int} alatBeratId, operatorXId, operatorYId, userId
     */
    private function seedSharedDatabase(): array
    {
        $originalDefault = config('database.default');
        config(['database.default' => 'm42_concurrency']);

        try {
            $this->artisan('migrate', ['--force' => true])->run();

            $alatBerat = AlatBerats::factory()->create();
            Silos::factory()->for($alatBerat, 'alatBerat')->create();

            $operatorX = Operators::factory()->create();
            Sios::factory()->for($operatorX, 'operator')->create();

            $operatorY = Operators::factory()->create();
            Sios::factory()->for($operatorY, 'operator')->create();

            $user = User::factory()->create(['job_title' => 'HSSE']);

            return [$alatBerat->id, $operatorX->id, $operatorY->id, $user->id];
        } finally {
            config(['database.default' => $originalDefault]);
        }
    }
}
