<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TECHNICAL_AUDIT.md H1: "one active assignment per equipment/operator"
     * was enforced only by PHP checks (AssignmentEligibility), never a DB
     * constraint — a race condition between two near-simultaneous requests
     * could pass the same eligibility check before either write lands.
     * IMPLEMENTATION_PLAN.md Milestone M4.2 closes this: application-level
     * checks stay (fast failure, a friendly message); this constraint is
     * the last line of defense that makes the invariant actually
     * impossible to violate, not just discouraged (Blueprint P6).
     *
     * MySQL has no partial/filtered unique index (unlike Postgres/SQLite's
     * native `WHERE` support), so this uses the standard MySQL-compatible
     * workaround: a generated column that is NULL unless is_active is
     * true, then a plain unique index on that column — MySQL (and SQLite)
     * treat multiple NULLs in a unique index as distinct, so only two
     * *active* rows for the same operator/equipment collide.
     */
    public function up(): void
    {
        Schema::table('operator_alat_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('active_operator_id')
                ->nullable()
                ->virtualAs('CASE WHEN is_active THEN operator_id ELSE NULL END')
                ->after('is_active');

            $table->unsignedBigInteger('active_alat_berat_id')
                ->nullable()
                ->virtualAs('CASE WHEN is_active THEN alat_berat_id ELSE NULL END')
                ->after('active_operator_id');
        });

        Schema::table('operator_alat_assignments', function (Blueprint $table) {
            $table->unique('active_operator_id', 'one_active_assignment_per_operator');
            $table->unique('active_alat_berat_id', 'one_active_assignment_per_alat_berat');
        });
    }

    public function down(): void
    {
        Schema::table('operator_alat_assignments', function (Blueprint $table) {
            $table->dropUnique('one_active_assignment_per_operator');
            $table->dropUnique('one_active_assignment_per_alat_berat');
        });

        Schema::table('operator_alat_assignments', function (Blueprint $table) {
            $table->dropColumn(['active_operator_id', 'active_alat_berat_id']);
        });
    }
};
