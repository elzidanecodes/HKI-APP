<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.3 / TECHNICAL_AUDIT.md M11: indexes
 * added on `silos.tanggal_expired`, `sios.tanggal_expired`, and
 * `operator_alat_assignments.is_active` by
 * database/migrations/2026_08_09_000000_add_indexes_to_compliance_and_assignment_tables.php.
 *
 * This asserts the index exists in the schema, driver-portable (doctrine/dbal
 * is not installed, so this reads each driver's own catalog directly rather
 * than a Schema::getIndexes()-style helper). It deliberately does not assert
 * on EXPLAIN output: SQLite (this suite's driver, per phpunit.xml) and MySQL
 * (production, per .env) have different query planners and different EXPLAIN
 * formats, so an EXPLAIN assertion here would only prove SQLite's planner
 * uses the index — not the thing this milestone actually needs proven. The
 * MySQL EXPLAIN verification this milestone's plan calls for is a manual,
 * pre-deploy step documented in the migration's own docblock, the same split
 * Milestone M4.2's concurrency test used for its SQLite/MySQL divergence.
 */
class IndexesForHotQueriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_silos_tanggal_expired_is_indexed(): void
    {
        $this->assertTrue($this->columnIsIndexed('silos', 'tanggal_expired'));
    }

    public function test_sios_tanggal_expired_is_indexed(): void
    {
        $this->assertTrue($this->columnIsIndexed('sios', 'tanggal_expired'));
    }

    public function test_operator_alat_assignments_is_active_is_indexed(): void
    {
        $this->assertTrue($this->columnIsIndexed('operator_alat_assignments', 'is_active'));
    }

    private function columnIsIndexed(string $table, string $column): bool
    {
        return match (DB::getDriverName()) {
            'sqlite' => $this->sqliteColumnIsIndexed($table, $column),
            'mysql' => $this->mysqlColumnIsIndexed($table, $column),
            default => throw new \RuntimeException('Unsupported driver for index verification: '.DB::getDriverName()),
        };
    }

    private function sqliteColumnIsIndexed(string $table, string $column): bool
    {
        $indexes = DB::select("PRAGMA index_list({$table})");

        foreach ($indexes as $index) {
            $columns = DB::select("PRAGMA index_info({$index->name})");

            if (collect($columns)->pluck('name')->contains($column)) {
                return true;
            }
        }

        return false;
    }

    private function mysqlColumnIsIndexed(string $table, string $column): bool
    {
        $rows = DB::select('SHOW INDEX FROM '.$table.' WHERE Column_name = ?', [$column]);

        return count($rows) > 0;
    }
}
