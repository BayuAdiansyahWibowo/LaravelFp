<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom diambil_at ke tabel pengirimen.
     */
    public function up(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->timestamp('diambil_at')->nullable()->after('status');
        });
    }

    /**
     * Rollback kolom diambil_at.
     */
    public function down(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->dropColumn('diambil_at');
        });
    }
};
