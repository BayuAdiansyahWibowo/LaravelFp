<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kendaraan;
use App\Models\Driver;
use App\Models\Pengiriman;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik utama
        $totalProduk = Produk::count();
        $totalKendaraan = Kendaraan::count();
        $totalDriver = Driver::count();

        // Pengiriman hari ini
        $pengirimanHariIni = Pengiriman::whereDate('created_at', Carbon::today())->count();

        // 5 pengiriman terbaru
        $tugasPengiriman = Pengiriman::with(['produk', 'driver', 'kendaraan'])
                            ->latest()
                            ->take(5)
                            ->get();

        // Data chart: pengiriman per hari selama 7 hari terakhir
        $dates = collect();
        $counts = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates->push($date->format('d M'));
            $counts->push(
                Pengiriman::whereDate('created_at', $date)->count()
            );
        }

        return view('dashboard', compact(
            'totalProduk', 'totalKendaraan', 'totalDriver',
            'pengirimanHariIni', 'tugasPengiriman', 'dates', 'counts'
        ));
    }
}
