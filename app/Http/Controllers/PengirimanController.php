<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\Kendaraan;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengiriman::with(['produk', 'kendaraan', 'driver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('produk', fn($q) => $q->where('nama', 'like', "%$search%"))
                  ->orWhereHas('driver', fn($q) => $q->where('nama', 'like', "%$search%"));
        }

        $pengirimans = $query->latest()->get();
        return view('pengiriman.index', compact('pengirimans'));
    }

    public function create()
    {
        $pengirimanAktif = Pengiriman::whereIn('status', ['menunggu_konfirmasi', 'sedang_pengiriman'])->get();


        $driverTerpakai = $pengirimanAktif->pluck('driver_id')->toArray();
        $kendaraanTerpakai = $pengirimanAktif->pluck('kendaraan_id')->toArray();

        $drivers = Driver::whereNotIn('id', $driverTerpakai)->get();
        $kendaraans = Kendaraan::whereNotIn('id', $kendaraanTerpakai)->get();
        $produks = Produk::all();

        $pusatLat = env('PUSAT_LAT');
        $pusatLng = env('PUSAT_LNG');

        return view('pengiriman.create', compact('produks', 'kendaraans', 'drivers', 'pusatLat', 'pusatLng'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id'         => 'required|exists:produks,id',
            'kendaraan_id'      => 'required|exists:kendaraans,id',
            'driver_id'         => 'required|exists:drivers,id',
            'jumlah'            => 'required|integer|min:1',
            'latitude_awal'     => 'required|numeric',
            'longitude_awal'    => 'required|numeric',
            'latitude_tujuan'   => 'required|numeric',
            'longitude_tujuan'  => 'required|numeric',
            'estimasi_jarak'    => 'required|numeric',
            'estimasi_waktu'    => 'required|numeric',
            'tujuan'            => 'required|string|max:255',      // ✅ Tambahan
            'jadwal_kirim'      => 'required|date',                // ✅ Tambahan
        ]);

        $produk = Produk::findOrFail($request->produk_id);
        $kendaraan = Kendaraan::findOrFail($request->kendaraan_id);

        // Cek stok
        if ($produk->stok < $request->jumlah) {
            return back()->withErrors(['stok' => 'Stok produk tidak mencukupi.'])->withInput();
        }

        // Konversi berat per unit ke kg
        $beratPerUnitKg = $produk->skala_berat === 'g' ? $produk->berat_per_unit / 1000 : $produk->berat_per_unit;
        $totalBerat = $beratPerUnitKg * $request->jumlah;

        // Generate nomor resi otomatis
        $tanggal = now()->format('Ymd');
        $counter = Pengiriman::whereDate('created_at', now()->toDateString())->count() + 1;
        $nomorResi = 'RESI-' . $tanggal . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);

        // Kurangi stok produk
        $produk->stok -= $request->jumlah;
        $produk->save();

        // Simpan pengiriman
        Pengiriman::create([
            'produk_id'         => $produk->id,
            'kendaraan_id'      => $kendaraan->id,
            'driver_id'         => $request->driver_id,
            'jumlah'            => $request->jumlah,
            'berat_per_unit'    => $beratPerUnitKg,
            'total_berat'       => $totalBerat,
            'latitude_awal'     => $request->latitude_awal,
            'longitude_awal'    => $request->longitude_awal,
            'latitude_tujuan'   => $request->latitude_tujuan,
            'longitude_tujuan'  => $request->longitude_tujuan,
            'plat_nomor'        => $kendaraan->plat_nomor,
            'ukuran_kendaraan'  => $kendaraan->ukuran,
            'jenis'             => $produk->jenis,
            'estimasi_jarak'    => $request->estimasi_jarak,
            'estimasi_waktu'    => $request->estimasi_waktu,
            'status'            => 'menunggu_konfirmasi',
            'nomor_resi'        => $nomorResi,
            'lokasi_pusat'      => json_encode([
                'lat' => env('PUSAT_LAT'),
                'lng' => env('PUSAT_LNG')
            ]),
            'tujuan'            => $request->tujuan,         // ✅ Tambahan
            'jadwal_kirim'      => $request->jadwal_kirim,   // ✅ Tambahan
        ]);

        return redirect()->route('pengiriman.index')
                         ->with('success', 'Pengiriman berhasil dibuat dan stok produk dikurangi.');
    }

    public function show(string $id)
    {
        $pengiriman = Pengiriman::with(['produk', 'kendaraan', 'driver'])->findOrFail($id);
        return view('pengiriman.show', compact('pengiriman'));
    }

    public function edit($id)
{
    $pengiriman = Pengiriman::findOrFail($id);
    $produks = Produk::all();
    $kendaraans = Kendaraan::all();
    $drivers = Driver::all();

    // Lokasi pusat dari .env
    $pusatLat = env('PUSAT_LAT', -6.200);
    $pusatLng = env('PUSAT_LNG', 106.816);

    return view('pengiriman.edit', compact(
        'pengiriman', 'produks', 'kendaraans', 'drivers', 'pusatLat', 'pusatLng'
    ));
}

public function update(Request $request, string $id)
{
    $request->validate([
        'produk_id'         => 'required|exists:produks,id',
        'kendaraan_id'      => 'required|exists:kendaraans,id',
        'driver_id'         => 'required|exists:drivers,id',
        'jumlah'            => 'required|integer|min:1',
        'latitude_awal'     => 'required|numeric',
        'longitude_awal'    => 'required|numeric',
        'latitude_tujuan'   => 'required|numeric',
        'longitude_tujuan'  => 'required|numeric',
        'estimasi_jarak'    => 'required|numeric',
        'estimasi_waktu'    => 'required|numeric',
        'tujuan'            => 'required|string',
        'jadwal_kirim'      => 'required|date',
    ]);

    $pengiriman = Pengiriman::findOrFail($id);
    $produk = Produk::findOrFail($request->produk_id);
    $kendaraan = Kendaraan::findOrFail($request->kendaraan_id);

    $beratPerUnitKg = $produk->skala_berat === 'g'
        ? $produk->berat_per_unit / 1000
        : $produk->berat_per_unit;

    $totalBerat = $beratPerUnitKg * $request->jumlah;

    $pengiriman->update([
        'produk_id'         => $produk->id,
        'kendaraan_id'      => $kendaraan->id,
        'driver_id'         => $request->driver_id,
        'jumlah'            => $request->jumlah,
        'berat_per_unit'    => $beratPerUnitKg,
        'total_berat'       => $totalBerat,
        'latitude_awal'     => $request->latitude_awal,
        'longitude_awal'    => $request->longitude_awal,
        'latitude_tujuan'   => $request->latitude_tujuan,
        'longitude_tujuan'  => $request->longitude_tujuan,
        'plat_nomor'        => $kendaraan->plat_nomor,
        'ukuran_kendaraan'  => $kendaraan->ukuran,
        'jenis'             => $produk->jenis,
        'estimasi_jarak'    => $request->estimasi_jarak,
        'estimasi_waktu'    => $request->estimasi_waktu,
        'status'            => 'menunggu_konfirmasi',
        'nomor_resi'        => $pengiriman->nomor_resi ?? 'RESI-' . strtoupper(Str::random(8)),
        'lokasi_pusat'      => json_encode([
            'lat' => env('PUSAT_LAT'),
            'lng' => env('PUSAT_LNG')
        ]),
        'tujuan'            => $request->tujuan,
        'jadwal_kirim'      => $request->jadwal_kirim,
    ]);

    return redirect()->route('pengiriman.index')
                     ->with('success', 'Pengiriman berhasil diperbarui.');
}

    public function destroy(string $id)
    {
        $pengiriman = Pengiriman::findOrFail($id);
        $pengiriman->delete();

        return redirect()->route('pengiriman.index')
                         ->with('success', 'Pengiriman berhasil dihapus.');
    }

    // Konfirmasi oleh driver
    public function konfirmasi(Request $request, $id)
{
     $driver = $request->attributes->get('driver');

    $pengiriman = Pengiriman::where('id', $id)
    ->where('status', 'menunggu_konfirmasi')
    ->where('driver_id', $driver->id)
    ->first();


    if (!$pengiriman) {
        return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
    }

    if ($pengiriman->status !== 'menunggu_konfirmasi') {
        return response()->json(['message' => 'Pengiriman sudah diproses sebelumnya.'], 400);
    }

    // ✅ Ubah status pengiriman dan tambahkan timestamp
    $pengiriman->status = 'sedang_pengiriman'; // konsisten snake_case
    $pengiriman->diambil_at = now();
    $pengiriman->save();

    // ✅ Update status driver
    $driver->status = 'sedang_pengiriman'; // sesuai endpoint update-status
    $driver->save();

    return response()->json([
        'message' => 'Pengiriman berhasil dikonfirmasi dan status driver diperbarui.'
    ]);
}

public function tolak(Request $request, $id)
{
    try {
        $driver = $request->attributes->get('driver');

        \Log::info('[DEBUG] Tolak dipanggil', [
            'driver_id' => $driver->id,
            'pengiriman_id' => $id,
        ]);

        $pengiriman = Pengiriman::where('id', $id)
            ->where('status', 'menunggu_konfirmasi')
            ->where('driver_id', $driver->id)
            ->first();

        if (!$pengiriman) {
            \Log::warning('[DEBUG] Pengiriman tidak ditemukan atau tidak valid', [
                'id' => $id,
                'driver_id' => $driver->id,
            ]);
            return response()->json(['message' => 'Tugas tidak ditemukan atau tidak valid'], 404);
        }

        $pengiriman->status = 'ditolak_oleh_driver';
        $pengiriman->selesai_at = now();  
        $pengiriman->save();

        $driver->status = 'tersedia';
        $driver->save();

        \Log::info('[DEBUG] Driver menolak tugas, tapi tidak mengosongkan driver_id');

        return response()->json(['message' => 'Tugas ditandai sebagai ditolak oleh driver.']);
    } catch (\Throwable $e) {
        \Log::error('[ERROR] Gagal tolak tugas', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['message' => 'Terjadi kesalahan server'], 500);
    }
}

public function mulaiJemput(Request $request, $id)
{
    $driver = $request->get('driver');
    $pengiriman = Pengiriman::where('id', $id)->where('driver_id', $driver->id)->first();

    if (!$pengiriman) {
        return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
    }

    $pengiriman->status = 'menjemput_barang';
    $pengiriman->diambil_at = now(); // ✅ Tambahkan ini
    $pengiriman->save();

    $driver->status = 'menjemput_barang';
    $driver->save();

    return response()->json(['message' => 'Status diperbarui ke menjemput barang']);
}


public function ambilBarang(Request $request, $id)
{
    $driver = $request->get('driver');
    $pengiriman = Pengiriman::where('id', $id)->where('driver_id', $driver->id)->first();

    if (!$pengiriman) {
        return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
    }

    $pengiriman->status = 'sedang_pengiriman';
    $pengiriman->save();

    $driver->status = 'sedang_pengiriman'; // ganti dari 'sedang_kirim'
    $driver->save();

    return response()->json([
    'message' => 'Status diperbarui ke sedang pengiriman',
    'status_pengiriman' => $pengiriman->status,
    'status_driver' => $driver->status
]);

}



    // Tandai sebagai selesai
    public function selesai(Request $request, $id)
{
    $pengiriman = Pengiriman::findOrFail($id);

    if ($pengiriman->status !== 'sedang_pengiriman') {
        return response()->json(['message' => 'Pengiriman belum dikirim atau sudah selesai.'], 400);
    }

    $driver = $request->attributes->get('driver');

    if (!$driver) {
        return response()->json(['message' => 'Driver tidak ditemukan dari token.'], 401);
    }

    // Logging untuk debug perbandingan ID driver
    \Log::info('Cek driver saat selesai pengiriman', [
        'pengiriman_id' => $pengiriman->id,
        'driver_token_id' => $driver->id,
        'pengiriman_driver_id' => $pengiriman->driver_id,
        'env' => app()->environment()
    ]);

    // Validasi driver hanya jika di environment production
    if (app()->environment('production') && $pengiriman->driver_id !== $driver->id) {
        return response()->json(['message' => 'Anda tidak berhak menyelesaikan pengiriman ini.'], 403);
    }

    $pengiriman->status = 'selesai';
    $pengiriman->selesai_at = now();
    $pengiriman->save();

    // Update status driver jadi tersedia
    $driverModel = $pengiriman->driver;
    if ($driverModel && $driverModel->status !== 'tersedia') {
        $driverModel->status = 'tersedia';
        $driverModel->save();
    }

    return response()->json(['message' => 'Pengiriman diselesaikan dan status driver diperbarui.']);
}

public function uploadBukti(Request $request, $id)
{
    $request->validate([
        'bukti' => 'required|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    $driver = $request->attributes->get('driver');
    if (!$driver) {
        return response()->json(['message' => 'Driver tidak ditemukan'], 401);
    }

    $pengiriman = Pengiriman::where('id', $id)
        ->where('driver_id', $driver->id)
        ->first();

    if (!$pengiriman) {
        return response()->json(['message' => 'Pengiriman tidak ditemukan'], 404);
    }

    if ($request->hasFile('bukti')) {
        $file = $request->file('bukti');
        $path = $file->store('bukti_pengiriman', 'public'); // simpan ke storage/app/public/bukti_pengiriman

        $pengiriman->bukti_pengiriman = $path;
        $pengiriman->save();

        return response()->json([
            'message' => 'Bukti pengiriman berhasil diunggah.',
            'path' => asset('storage/' . $path)
        ]);
    }

    return response()->json(['message' => 'File bukti tidak ditemukan'], 400);
}

    // Tambahkan metode lainnya sesuai kebutuhan
    
    public function laporan()
{
    $pengirimans = Pengiriman::with(['produk', 'kendaraan', 'driver'])
        ->where('status', 'selesai')
        ->orderByDesc('selesai_at')
        ->get();

    return view('pengiriman.index', compact('pengirimans'));
}

public function riwayat(Request $request)
{
    $driver = $request->get('driver'); // Didapat dari middleware jwt.auth

    if (!$driver) {
        return response()->json(['message' => 'Driver tidak ditemukan'], 404);
    }

    $riwayat = \App\Models\Pengiriman::where('driver_id', $driver->id)
        ->where('status', 'selesai')
        ->orderByDesc('selesai_at')
        ->get([
            'id',
            'nomor_resi',
            'tujuan',
            'selesai_at',
            'status',
            'bukti_pengiriman'
        ]);

    return response()->json($riwayat);
}


}