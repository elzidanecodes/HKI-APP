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
    public function up()
    {
        Schema::create('alat_berats', function (Blueprint $table) {
            $table->id();

            // Identitas alat
            $table->string('kode_alat')->unique(); 
            // contoh: EXC-001, CRN-002 dll.

            $table->string('nama_alat', 100);
            $table->string('merk_alat', 50);
            $table->string('tipe_alat', 50);

            // Tahun produksi (bukan tanggal)
            $table->year('tahun_produksi')->nullable();

            // Lokasi / STA proyek
            $table->string('sta_lokasi', 50)->nullable();
            // contoh: STA 12+500, Gudang A, Workshop

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
        Schema::dropIfExists('alat_berats');
    }
};