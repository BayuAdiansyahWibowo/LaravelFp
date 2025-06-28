<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Events\DriverLocationUpdated; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    // ===================== ADMIN WEB =========================
    public function index(Request $request)
    {
        $query = Driver::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $drivers = $query->get();
        return view('driver.index', compact('drivers'));
    }

    public function create()
    {
        return view('driver.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
            'sim' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'alamat' => 'required|string',
            'status' => 'required|in:tersedia,sedang_pengiriman,tidak_aktif,menjemput_barang',
        ]);

        $simPath = null;
        if ($request->hasFile('sim')) {
            if (!$request->file('sim')->isValid()) {
                Log::error('SIM upload error', ['error' => $request->file('sim')->getErrorMessage()]);
                return response()->json(['message' => 'File SIM tidak valid.'], 422);
            }
            $simPath = $request->file('sim')->store('sim', 'public');
        }

        Driver::create([
            'nama' => $request->nama,
            'nomor_telepon' => $request->nomor_telepon,
            'sim_path' => $simPath,
            'alamat' => $request->alamat,
            'status' => $request->status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('driver.index')->with('success', 'Driver berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        return view('driver.edit', compact('driver'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
            'sim' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'alamat' => 'required|string',
            'status' => 'required|in:tersedia,sedang_pengiriman,tidak_aktif,menjemput_barang',
        ]);

        $driver = Driver::findOrFail($id);

        $data = $request->only(['nama', 'nomor_telepon', 'alamat', 'status']);

        if ($request->filled('latitude')) {
            $data['latitude'] = $request->latitude;
        }

        if ($request->filled('longitude')) {
            $data['longitude'] = $request->longitude;
        }

        if ($request->hasFile('sim')) {
            $data['sim_path'] = $request->file('sim')->store('sim', 'public');
        }

        $driver->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Driver berhasil diperbarui.',
                'driver' => $driver,
                'sim_url' => $driver->sim_path ? asset('storage/' . $driver->sim_path) : null
            ]);
        }

        return redirect()->route('driver.index')->with('success', 'Driver berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();

        return redirect()->back()->with('success', 'Driver berhasil dihapus.');
    }

    // ===================== MOBILE JWT =========================

    public function storeFromMobile(Request $request)
    {
        $driver = $request->attributes->get('driver');  // Dari middleware jwt.auth

        if (!$driver) {
            return response()->json(['message' => 'Driver tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'alamat'         => 'required|string',
            'nomor_telepon'  => 'required|string|max:20',
            'sim_path'       => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
        ]);

        // Simpan file jika ada
        if ($request->hasFile('sim_path')) {
            $path = $request->file('sim_path')->store('sim', 'public');
            $driver->sim_path = $path;
        }

        $driver->alamat = $validated['alamat'];
        $driver->nomor_telepon = $validated['nomor_telepon'];
        $driver->latitude = $validated['latitude'] ?? $driver->latitude;
        $driver->longitude = $validated['longitude'] ?? $driver->longitude;
        $driver->save();

        return response()->json([
            'message' => 'Data driver berhasil disimpan',
            'data' => [
                'nama' => $driver->nama,
                'email' => $driver->email,
                'alamat' => $driver->alamat,
                'nomor_telepon' => $driver->nomor_telepon,
                'sim_path' => $driver->sim_path ? asset('storage/' . $driver->sim_path) : null,
            ]
        ]);
    }

    public function updateFromMobile(Request $request)
    {
        $driver = $request->attributes->get('driver');

        if (!$driver) {
            return response()->json(['message' => 'Driver tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'alamat'         => 'required|string',
            'nomor_telepon'  => 'required|string|max:20',
            'sim_path'       => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
        ]);

        if ($request->hasFile('sim_path')) {
            $path = $request->file('sim_path')->store('sim', 'public');
            $driver->sim_path = $path;
        }

        $driver->alamat = $validated['alamat'];
        $driver->nomor_telepon = $validated['nomor_telepon'];
        $driver->latitude = $validated['latitude'] ?? $driver->latitude;
        $driver->longitude = $validated['longitude'] ?? $driver->longitude;
        $driver->save();

        return response()->json([
            'message' => 'Data driver berhasil diperbarui',
            'data' => [
                'nama' => $driver->nama,
                'email' => $driver->email,
                'alamat' => $driver->alamat,
                'nomor_telepon' => $driver->nomor_telepon,
                'sim_path' => $driver->sim_path ? asset('storage/' . $driver->sim_path) : null,
            ]
        ]);
    }

    public function updateLocation(Request $request)
{
    $request->validate([
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
    ]);

    $driver = $request->attributes->get('driver');
; // ✅ ambil dari token

    if (!$driver) {
        return response()->json(['message' => 'Driver tidak ditemukan (token tidak valid)'], 401);
    }

    $driver->latitude = $request->latitude;
    $driver->longitude = $request->longitude;
    $driver->save();

    // 🔴 Tambahkan broadcast lokasi real-time ke frontend
    try {
    broadcast(new DriverLocationUpdated(
        $driver->id,
        $driver->latitude,
        $driver->longitude
    ))->toOthers();
} catch (\Throwable $e) {
    Log::error('[Broadcast Error]', ['msg' => $e->getMessage()]);
    // tidak return error agar tracking tetap lanjut
}


    Log::info('Driver location updated', [
        'id' => $driver->id,
        'latitude' => $driver->latitude,
        'longitude' => $driver->longitude,
    ]);

    return response()->json([
        'message' => 'Lokasi berhasil diperbarui.',
        'driver' => $driver
    ]);
}

    public function me(Request $request)
{
    // Ambil data driver dari request (hasil dari middleware jwt.auth)
    $driver = $request->attributes->get('driver');

    if (!$driver) {
        return response()->json([
            'message' => 'Driver tidak ditemukan'
        ], 404);
    }

    // Cek kelengkapan profil
    $isComplete = $driver->alamat && $driver->nomor_telepon && $driver->sim_path;

    // Kembalikan response dengan data yang sudah diformat
    return response()->json([
        'message' => 'Data driver ditemukan',
        'data' => [
            'nama' => $driver->nama,
            'email' => $driver->email,
            'alamat' => $driver->alamat,
            'nomor_telepon' => $driver->nomor_telepon,
            'sim_path' => $driver->sim_path ? asset('storage/' . $driver->sim_path) : null,
            'profil_lengkap' => $isComplete
        ]
    ]);
}
}