<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TECHNICAL_AUDIT.md M11: `tanggal_expired` and the assignment table's
     * `is_active` are filtered on nearly every request — DocumentValidity-
     * backed scopes (Silos::expired(), Sios::expired()/currentlyValid()),
     * the dashboard widgets (HseStats, ExpiredDocuments,
     * ActiveAssignmentsBySta), AssignOperator's eligibility check, and the
     * nightly AutoEndIneligibleAssignments sweep — but neither column
     * carried an index. IMPLEMENTATION_PLAN.md Milestone M4.3 closes this.
     *
     * `operator_id` (sios) and `alat_berat_id` (silos, operator_alat_assignments)
     * are not indexed here: MySQL/InnoDB has indexed those columns since
     * their `foreignId()->constrained()` definitions, because InnoDB
     * requires an index on any column carrying a foreign key constraint.
     * Adding a second one would violate the Complexity Budget (Blueprint
     * P1) — it protects nothing that isn't already protected.
     *
     * Verification for this milestone's stated testing requirement ("EXPLAIN
     * on the hot queries before/after, confirming index usage") is split in
     * the same way Milestone M4.2 split its verification: the portable,
     * automated half lives in
     * tests/Feature/IndexesForHotQueriesTest.php, which asserts the index
     * exists in the schema regardless of driver. The EXPLAIN step itself is
     * a manual, pre-deploy check against the real engine — SQLite's query
     * planner and EXPLAIN output are not equivalent to MySQL's (this
     * project's production driver), so an automated EXPLAIN assertion here
     * would verify SQLite's planner, not the one that matters. Before
     * deploying, run against a MySQL copy of this schema:
     *
     *   EXPLAIN SELECT * FROM silos WHERE tanggal_expired < CURDATE();
     *   EXPLAIN SELECT * FROM sios WHERE tanggal_expired < CURDATE();
     *   EXPLAIN SELECT * FROM operator_alat_assignments WHERE is_active = 1;
     *
     * and confirm each plan's `key` column names the new index (`possible_keys`
     * containing it is not sufficient — `key` is what the optimizer actually
     * chose) instead of `type: ALL` (full table scan).
     */
    public function up(): void
    {
        Schema::table('silos', function (Blueprint $table) {
            $table->index('tanggal_expired');
        });

        Schema::table('sios', function (Blueprint $table) {
            $table->index('tanggal_expired');
        });

        Schema::table('operator_alat_assignments', function (Blueprint $table) {
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('silos', function (Blueprint $table) {
            $table->dropIndex(['tanggal_expired']);
        });

        Schema::table('sios', function (Blueprint $table) {
            $table->dropIndex(['tanggal_expired']);
        });

        Schema::table('operator_alat_assignments', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
    }
};
