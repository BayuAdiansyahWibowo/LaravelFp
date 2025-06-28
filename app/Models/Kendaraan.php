<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi secara massal
    protected $fillable = ['nama', 'plat_nomor', 'ukuran', 'kapasitas'];

    // (Opsional) Relasi: Satu kendaraan bisa dimiliki oleh banyak driver
    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
}
