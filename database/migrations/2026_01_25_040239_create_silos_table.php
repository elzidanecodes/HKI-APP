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
       Schema::create('silos', function (Blueprint $table) {
            $table->id();

            // Relasi ke alat berat
            $table->foreignId('alat_berat_id')
                ->constrained('alat_berats')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Nomor dokumen SILO
            $table->string('nomor_silo')->unique();

            // User input
            $table->date('tanggal_terbit'); // tanggal terbit

            // Sistem hitung
            $table->date('tanggal_expired'); // issued_at + 1 tahun

            // Dokumen pendukung
            $table->string('file_path')->nullable();

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
        Schema::dropIfExists('silos');
    }
};
