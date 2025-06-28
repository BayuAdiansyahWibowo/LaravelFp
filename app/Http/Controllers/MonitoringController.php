<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Models\Driver;
use Illuminate\Support\Facades\Log; // Import the Log facade

class MonitoringController extends Controller
{
    public function index()
    {
        // Fetch drivers. Eager load 'kendaraan' for display in markers/popups.
        // Filter to only include drivers who have valid latitude/longitude recorded.
        $drivers = Driver::with('kendaraan')
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->get();

        // Fetch pengirimen. Eager load related models for comprehensive display.
        // It's good practice to filter pengirimen based on their status
        // to show only relevant, ongoing shipments on the map.
        $pengirimen = Pengiriman::with(['produk', 'driver', 'kendaraan'])
                                ->whereIn('status', ['dikirim', 'menunggu_konfirmasi']) // Example: only show active/pending shipments
                                ->get();

        // --- Retrieve central location coordinates from .env ---
        $pusatLat = env('PUSAT_LAT');
        $pusatLng = env('PUSAT_LNG');

        // Add a safeguard: If .env variables are not set, provide default coordinates.
        // This prevents errors and helps during development.
        if (empty($pusatLat) || empty($pusatLng)) {
            $pusatLat = -6.2000; // Default Latitude for Central Jakarta
            $pusatLng = 106.8160; // Default Longitude for Central Jakarta
            Log::warning('PUSAT_LAT or PUSAT_LNG is not set in your .env file. Using default Jakarta coordinates.');
        }

        // Pass all necessary data to the view
        return view('monitoring.index', compact('drivers', 'pengirimen', 'pusatLat', 'pusatLng'));
    }
}