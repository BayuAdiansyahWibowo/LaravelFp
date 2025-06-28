<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['pangan', 'elektronik']);
            $table->integer('stok');
            $table->float('berat_per_unit')->default(0); // berat per item
            $table->enum('skala_berat', ['kg', 'g'])->default('kg'); // satuan berat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
