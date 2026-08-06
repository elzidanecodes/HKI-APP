<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TECHNICAL_AUDIT.md H7: hard DeleteAction present on 4 Edit pages,
     * zero SoftDeletes — legal documents and their subjects were
     * permanently deletable by any authenticated user.
     * IMPLEMENTATION_PLAN.md Milestone M3.5 adds soft-delete support so
     * deletion becomes recoverable rather than destructive.
     */
    private const TABLES = ['operators', 'alat_berats', 'silos', 'sios'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
