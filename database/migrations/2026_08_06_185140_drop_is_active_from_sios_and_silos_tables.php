<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contract step of the C2 expand→migrate→contract sequence
     * (TECHNICAL_AUDIT.md C2, ARCHITECTURE_BLUEPRINT.md §10). The expand
     * and migrate steps landed in Phase 2 (M2.2–M2.5): DocumentStatus is
     * now derived from DocumentValidity everywhere, and no code path reads
     * or writes `is_active` on sios/silos. This drops the dead column.
     */
    public function up(): void
    {
        Schema::table('silos', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('sios', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('silos', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        Schema::table('sios', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
    }
};
