<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PengirimanController;
use App\Helpers\JwtHelper;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route di bawah ini diakses oleh aplikasi mobile (driver).
| Sebagian besar menggunakan autentikasi JWT dengan middleware custom.
*/

/*
|--------------------------------------------------------------------------
| Public Routes (Tanpa Token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'apiRegister']);
Route::post('/forgot-password', [AuthController::class, 'forgot']);
Route::post('/reset-password', [AuthController::class, 'resetPasswordFromApp']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Harus kirim Bearer Token di header)
|--------------------------------------------------------------------------
| Gunakan middleware jwt.auth dan jwt.refresh untuk validasi + auto refresh
*/
 // 🌍 Endpoint untuk panel admin monitoring live lokasi driver
Route::get('/admin/driver-lokasi', function () {
    return \App\Models\Driver::select('id', 'nama', 'latitude', 'longitude', 'status')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->where('status', '!=', 'tidak_aktif')
        ->get();
});


Route::middleware(['jwt.auth', 'jwt.refresh'])->group(function () {

    // Informasi user dan driver
    Route::get('/auth/user-info', [AuthController::class, 'apiUser']);
    Route::get('/driver/data', [DriverController::class, 'profile']);

    Route::get('/driver/me', function (Request $request) {
        $driver = $request->get('driver');

        if (!$driver) {
            return response()->json(['message' => 'Driver tidak ditemukan'], 404);
        }

        // Cek kelengkapan
        $missing = [];

        if ($driver->alamat === '-' || !$driver->alamat) $missing[] = 'alamat';
        if ($driver->nomor_telepon === '-' || !$driver->nomor_telepon) $missing[] = 'nomor_telepon';
        if (!$driver->sim_path) $missing[] = 'foto SIM';

        if (count($missing) > 0) {
            return response()->json([
                'message' => 'Profil driver belum lengkap',
                'missing_fields' => $missing,
                'data' => $driver
            ], 200);
        }

        return response()->json([
            'message' => 'Profil driver lengkap',
            'data' => $driver
        ]);
    });

    // 🔔 Cek apakah ada pengiriman baru
    Route::get('/driver/pengiriman-baru', function (Request $request) {
    $driver = $request->get('driver');

    if (!$driver) {
        return response()->json(['message' => 'Driver tidak ditemukan'], 404);
    }

    $pengiriman = \App\Models\Pengiriman::with('produk')
        ->where('driver_id', $driver->id)
        ->where('status', 'menunggu_konfirmasi') // ✅ Ganti dari 'baru'
        ->latest()
        ->first();

    if ($pengiriman) {
        return response()->json([
            'message' => 'Ada pengiriman baru',
            'pengiriman' => [
                'id' => $pengiriman->id,
                'tujuan' => $pengiriman->tujuan,
                'jadwal_kirim' => $pengiriman->jadwal_kirim,
                'produk_nama' => $pengiriman->produk->nama ?? '-',
            ]
        ]);
    }

    return response()->json(['message' => 'Tidak ada pengiriman baru'], 204);
});


    // 📦 Ambil daftar pengiriman milik driver
    Route::get('/driver/pengiriman-ku', function (Request $request) {
        $driver = $request->attributes->get('driver');

        if (!$driver) {
            return response()->json(['message' => 'Driver tidak ditemukan'], 404);
        }

        $pengiriman = \App\Models\Pengiriman::with('produk')
            ->where('driver_id', $driver->id)
            ->latest()
            ->get()
->map(function ($p) {
    $data = [
        'id' => $p->id,
        'produk_nama' => $p->produk->nama ?? '-',
        'tujuan' => $p->tujuan,
        'jadwal_kirim' => $p->jadwal_kirim,
        'status' => $p->status,
        'latitude_tujuan' => $p->latitude_tujuan,
        'longitude_tujuan' => $p->longitude_tujuan,
    ];

    // ✅ Tambahkan lokasi pusat jika statusnya menjemput
    if ($p->status === 'menjemput_barang') {

        $data['latitude_pusat'] = env('PUSAT_LAT');
        $data['longitude_pusat'] = env('PUSAT_LNG');
    }

    return $data;
});



        return response()->json($pengiriman);
    });

    // ✅ Update status driver: tersedia / sedang_kirim / tidak_aktif
    Route::post('/driver/update-status', function (Request $request) {
        $request->validate([
            'status' => 'required|in:tersedia,sedang_kirim,tidak_aktif'
        ]);

        $driver = $request->get('driver');

        if (!$driver) {
            return response()->json(['message' => 'Driver tidak ditemukan'], 404);
        }

        $driver->status = $request->status;
        $driver->save();

        return response()->json(['message' => 'Status driver diperbarui']);
    });

    // 📍 Update lokasi driver
    Route::post('/driver/update-location', [DriverController::class, 'updateLocation']);

    // 📝 Simpan atau update profil driver dari mobile
    Route::post('/driver/store', [DriverController::class, 'storeFromMobile']);
    Route::post('/driver/update', [DriverController::class, 'updateFromMobile']);

    // 🔁 Konfirmasi dan Selesai pengiriman
    Route::post('/pengiriman/{id}/konfirmasi', [PengirimanController::class, 'konfirmasi']);
    Route::post('/pengiriman/{id}/selesai', [PengirimanController::class, 'selesai']);
    Route::post('/pengiriman/{id}/tolak', [PengirimanController::class, 'tolak']
    );
    Route::post('/pengiriman/{id}/upload-bukti', [PengirimanController::class, 'uploadBukti']); // ✅ TAMBAHKAN INI

    // 🚪 Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // 🧪 Tes JWT
    Route::get('/test-jwt', [JwtHelper::class, 'test']);
    
    Route::get('/driver/riwayat-pengiriman', [PengirimanController::class, 'riwayat']);

// 🔄 Mulai jemput barang (ubah status ke driver_menjemput_barang)
Route::post('/pengiriman/{id}/mulai-jemput', [PengirimanController::class, 'mulaiJemput']);

// 📦 Ambil barang (ubah status ke sedang_pengiriman)
Route::post('/pengiriman/{id}/ambil-barang', [PengirimanController::class, 'ambilBarang']);

   
});
