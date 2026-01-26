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
        Schema::create('sios', function (Blueprint $table) {
            $table->id();

            // Relasi ke operator
            $table->foreignId('operator_id')
                ->constrained('operators')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Data legal SIO
            $table->string('nomor_sio')->unique();

            $table->date('tanggal_expired');

            // Dokumen
            $table->string('file_sio')->nullable();

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
        Schema::dropIfExists('sios');
    }
};
