<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('operator_alat_assignments', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('operator_id')
                ->constrained('operators')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('alat_berat_id')
                ->constrained('alat_berats')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Waktu penugasan
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();

            // Status aktif
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operator_alat_assignments');
    }
};