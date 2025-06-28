<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengirimansTable extends Migration
{
    public function up()
    {
        Schema::create('pengirimans', function (Blueprint $table) {
            $table->id();

            // Nomor resi unik
            $table->string('nomor_resi')->unique();

            // Relasi produk
            $table->unsignedBigInteger('produk_id');
            $table->string('jenis')->nullable(); // jenis produk dari master

            // Jumlah dan berat
            $table->integer('jumlah');
            $table->float('berat_per_unit'); // dalam kilogram
            $table->float('total_berat')->nullable(); // total berat
            $table->enum('skala_berat', ['kg', 'g'])->default('kg');

            // Relasi kendaraan dan driver
            $table->unsignedBigInteger('kendaraan_id');
            $table->string('plat_nomor')->nullable();
            $table->string('ukuran_kendaraan')->nullable();
            $table->unsignedBigInteger('driver_id');

            // Lokasi awal & tujuan
            $table->decimal('latitude_awal', 10, 7);
            $table->decimal('longitude_awal', 10, 7);
            $table->decimal('latitude_tujuan', 10, 7);
            $table->decimal('longitude_tujuan', 10, 7);

            // Estimasi jarak dan waktu
            $table->decimal('estimasi_jarak', 8, 2)->nullable(); // km
            $table->decimal('estimasi_waktu', 8, 2)->nullable(); // menit

            // Status pengiriman
            $table->string('status')->default('menunggu_konfirmasi');

            $table->timestamps();

            // Foreign keys
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
            $table->foreign('kendaraan_id')->references('id')->on('kendaraans')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengirimans');
    }
}
