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
        Schema::create('operators', function (Blueprint $table) {
            $table->id('id_operator'); //id() menghasilkan tipe data big int unsigned
            $table->unsignedBigInteger('nomor_silo'); // unsignedBigInteger() menghasilkan tipe data big int unsigned
            $table->string('nama_operator', 50);
            $table->string('nama_alat', 35);
            $table->string('merk_alat', 35);
            $table->string('tipe_alat', 20);
            $table->year('tahun_produksi');
            $table->string('foto_sio')->nullable();
            $table->string('foto_silo')->nullable();
            $table->string('nomor_hp', 15);
            $table->timestamps();

            $table->foreign('nomor_silo')->references('nomor_silo')->on('alat_berats')
            ->onUpdate('cascade')->onDelete('restrict');

        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operators');
    }
};
