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
            $table->id('id_logistik');

            $table->string('nama_barang', 100);
            $table->string('kategori_barang', 50);

            $table->text('deskripsi_barang')->nullable();

            $table->integer('jumlah_barang')->default(0);
            $table->string('satuan', 20);

            $table->string('lokasi')->nullable();

            $table->string('nama_vendor', 100)->nullable();

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