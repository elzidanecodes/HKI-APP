<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expand step closing part of TECHNICAL_AUDIT.md finding M9: sios has
     * no tanggal_terbit column, so Sios::validity() and
     * DocumentValidityMapper used created_at as a stand-in period start
     * (IMPLEMENTATION_PLAN.md Milestone M2.7). Existing rows are
     * backfilled from created_at so this migration changes zero currently
     * computed validity results.
     *
     * Left nullable at the schema level: enforcing NOT NULL would need
     * doctrine/dbal for the column-modify step, and adding a dependency
     * isn't justified for this milestone (Blueprint P1). Every future SIO
     * created through SiosResource's form (required field, this
     * milestone) or the factory always supplies it.
     */
    public function up(): void
    {
        Schema::table('sios', function (Blueprint $table) {
            $table->date('tanggal_terbit')->nullable()->after('nomor_sio');
        });

        DB::table('sios')->whereNull('tanggal_terbit')->update([
            'tanggal_terbit' => DB::raw('date(created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('sios', function (Blueprint $table) {
            $table->dropColumn('tanggal_terbit');
        });
    }
};
