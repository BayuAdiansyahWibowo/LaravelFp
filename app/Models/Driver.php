<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Driver extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'user_id', 
        'nama',
        'email',
        'nomor_telepon',
        'sim_path',
        'alamat',
        'latitude',
        'longitude',
        'status'
    ];

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}
}
