<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('pengirimans', function (Blueprint $table) {
        $table->string('bukti_pengiriman')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('pengirimans', function (Blueprint $table) {
        $table->dropColumn('bukti_pengiriman');
    });
}

};
