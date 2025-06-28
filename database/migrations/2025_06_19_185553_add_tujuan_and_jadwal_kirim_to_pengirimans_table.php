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
    Schema::table('pengirimans', function (Blueprint $table) {
        $table->string('tujuan')->nullable()->after('longitude_tujuan');
        $table->dateTime('jadwal_kirim')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('pengirimans', function (Blueprint $table) {
        $table->dropColumn('tujuan');
        $table->dropColumn('jadwal_kirim');
    });
}

};