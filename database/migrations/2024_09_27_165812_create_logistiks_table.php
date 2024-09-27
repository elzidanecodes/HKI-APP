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
        Schema::create('logistiks', function (Blueprint $table) {
            $table->id('id_logistik'); //id() menghasilkan tipe data big int unsigned
            $table->string('nama_barang', 50);
            $table->string('kategori_barang', 35);
            $table->string('deskripsi_barang', 35);
            $table->integer('jumlah_barang');
            $table->string('nama_vendor', 50);
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
        Schema::dropIfExists('logistiks');
    }
};
