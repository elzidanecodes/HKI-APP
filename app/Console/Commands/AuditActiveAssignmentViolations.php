<?php

namespace App\Console\Commands;

use App\Models\OperatorAlatAssignment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * IMPLEMENTATION_PLAN.md Milestone M4.1: de-risks I5's highest-likelihood
 * failure mode — Milestone M4.2 adds a unique/partial index enforcing
 * "one active assignment per operator_id" and "one active assignment
 * per alat_berat_id" on operator_alat_assignments. A unique constraint
 * fails to apply if any existing row already violates it, so this finds
 * violations first, against a real (ideally production-snapshot)
 * dataset, before that migration is written.
 *
 * One-off diagnostic tooling (Blueprint §4 Support: "Bebas"), not a
 * permanent Domain/Application citizen — it checks whether the
 * currently *persisted* data already violates a structural invariant,
 * not a business rule over input values (P1: no abstraction without a
 * concrete need; a one-off audit script run ahead of one migration
 * doesn't have one). Output is reviewed manually per this milestone's
 * own testing note — there is no automated test for this command.
 */
class AuditActiveAssignmentViolations extends Command
{
    protected $signature = 'assignment:audit-active-violations {--connection= : The database connection to audit (defaults to the configured connection — point this at a production snapshot)}';

    protected $description = 'Find operator_alat_assignments rows that already violate the one-active-assignment-per-operator/alat-berat invariant Milestone M4.2 will enforce as a DB constraint';

    public function handle(): int
    {
        $operatorViolations = $this->violatingIds('operator_id');
        $alatBeratViolations = $this->violatingIds('alat_berat_id');

        $this->reportViolations('operator_id', $operatorViolations);
        $this->reportViolations('alat_berat_id', $alatBeratViolations);

        if ($operatorViolations->isEmpty() && $alatBeratViolations->isEmpty()) {
            $this->info('No violations found. Safe to proceed to Milestone M4.2 (the unique constraint).');

            return self::SUCCESS;
        }

        $this->error(
            "Violations found — Milestone M4.2's constraint migration will fail against this data. ".
            "Resolve every row above (with the business-process owner's sign-off) before proceeding."
        );

        return self::FAILURE;
    }

    /**
     * @return Builder<OperatorAlatAssignment>
     */
    private function query(): Builder
    {
        $connection = $this->option('connection');

        return $connection
            ? OperatorAlatAssignment::on($connection)->newQuery()
            : OperatorAlatAssignment::query();
    }

    private function violatingIds(string $column): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->select($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($column);
    }

    private function reportViolations(string $column, Collection $violatingIds): void
    {
        if ($violatingIds->isEmpty()) {
            $this->info("No {$column} violations.");

            return;
        }

        $this->warn("{$violatingIds->count()} {$column} value(s) have more than one active assignment:");

        $rows = $this->query()
            ->whereIn($column, $violatingIds)
            ->where('is_active', true)
            ->orderBy($column)
            ->get(['id', 'operator_id', 'alat_berat_id', 'tanggal_mulai', 'created_at']);

        $this->table(
            ['Assignment ID', 'Operator ID', 'Alat Berat ID', 'Tanggal Mulai', 'Created At'],
            $rows->map(fn (OperatorAlatAssignment $row) => [
                $row->id,
                $row->operator_id,
                $row->alat_berat_id,
                $row->tanggal_mulai,
                $row->created_at,
            ])->all(),
        );
    }
}
