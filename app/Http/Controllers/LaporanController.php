<?php
namespace App\Http\Controllers;
use App\Models\Pengiriman;
use App\Models\Driver;
use Illuminate\Http\Request;


class LaporanController extends Controller
{
    public function index(Request $request)
{
    $query = Pengiriman::with(['produk', 'driver'])
        ->where('status', 'selesai');

    // ✅ Filter berdasarkan driver jika dipilih
    if ($request->filled('driver_id')) {
        $query->where('driver_id', $request->driver_id);
    }

    $pengirimans = $query->orderByDesc('selesai_at')->get();
    $drivers = Driver::orderBy('nama')->get(); // Untuk dropdown driver

    return view('laporan.index', compact('pengirimans', 'drivers'));
}
}
