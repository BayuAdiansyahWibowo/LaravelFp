<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengirimans';

    protected $fillable = [
        'produk_id',
        'kendaraan_id',
        'driver_id',
        'jumlah',
        'berat_per_unit',
        'total_berat',
        'latitude_awal',
        'longitude_awal',
        'latitude_tujuan',
        'longitude_tujuan',
        'plat_nomor',
        'ukuran_kendaraan',
        'jenis',
        'estimasi_jarak',
        'estimasi_waktu',
        'nomor_resi',
        'tujuan',
        'jadwal_kirim',
        'bukti_pengiriman',
        'status',
        'selesai_at'
    ];

protected $casts = [
    'selesai_at' => 'datetime:Y-m-d H:i:s',
];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}

