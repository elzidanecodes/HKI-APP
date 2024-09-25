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
            $table->id('id_alat');
            $table->string('nama_alat', 35);
            $table->string('merk_alat', 35);
            $table->string('tipe_alat', 20);
            $table->year('tahun_produksi');
            $table->string('foto_sio');
            $table->string('foto_silo');
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
