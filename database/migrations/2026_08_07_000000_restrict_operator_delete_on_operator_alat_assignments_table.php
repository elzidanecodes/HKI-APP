<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TECHNICAL_AUDIT.md C4: operator_id cascadeOnDelete() destroys
     * assignment history (audit-relevant data) when an Operator is
     * deleted, asymmetric with alat_berat_id's restrictOnDelete().
     * IMPLEMENTATION_PLAN.md Milestone M3.5 makes them symmetric.
     *
     * SQLite has no ALTER TABLE support for foreign key constraints
     * (Schema::dropForeign() throws BadMethodCallException there — no
     * doctrine/dbal installed to paper over it, and adding that
     * dependency isn't justified for this one migration, Blueprint P1),
     * so this rebuilds the table instead: the standard SQLite-compatible
     * technique, and one that also works unchanged against the
     * production MySQL connection.
     */
    private const COLUMNS = 'id, operator_id, alat_berat_id, tanggal_mulai, tanggal_selesai, is_active, created_at, updated_at';

    public function up(): void
    {
        Schema::rename('operator_alat_assignments', 'operator_alat_assignments_old');

        Schema::create('operator_alat_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('operator_id')
                ->constrained('operators')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('alat_berat_id')
                ->constrained('alat_berats')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement(
            'INSERT INTO operator_alat_assignments ('.self::COLUMNS.') '.
            'SELECT '.self::COLUMNS.' FROM operator_alat_assignments_old'
        );

        Schema::drop('operator_alat_assignments_old');
    }

    public function down(): void
    {
        Schema::rename('operator_alat_assignments', 'operator_alat_assignments_new');

        Schema::create('operator_alat_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('operator_id')
                ->constrained('operators')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('alat_berat_id')
                ->constrained('alat_berats')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement(
            'INSERT INTO operator_alat_assignments ('.self::COLUMNS.') '.
            'SELECT '.self::COLUMNS.' FROM operator_alat_assignments_new'
        );

        Schema::drop('operator_alat_assignments_new');
    }
};
