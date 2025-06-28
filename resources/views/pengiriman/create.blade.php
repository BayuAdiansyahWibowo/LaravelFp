@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Buat Pengiriman</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Message Box for user feedback --}}
    <div id="message-box" class="alert alert-warning d-none" role="alert"></div>

    <form action="{{ route('pengiriman.store') }}" method="POST">
        @csrf

        {{-- Produk --}}
        <div class="mb-3">
            <label for="produk_id" class="form-label">Pilih Produk</label>
            <select name="produk_id" id="produk_id" class="form-select" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($produks as $produk)
                    <option
                        value="{{ $produk->id }}"
                        data-stok="{{ $produk->stok }}"
                        data-jenis="{{ $produk->jenis }}"
                        data-berat="{{ $produk->berat_per_unit }}"
                        data-skala="{{ $produk->skala_berat }}">
                        {{ $produk->nama }}
                    </option>
                @endforeach
            </select>
            <div class="mt-2">
                <strong>Stok: <span id="stok-display">-</span></strong><br>
                <strong>Jenis: <span id="jenis-display">-</span></strong><br>
                <strong>Berat per Unit: <span id="berat-display">-</span></strong>
            </div>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah Produk</label>
            <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Total Berat (kg)</label>
            <input type="text" id="total_berat_display" class="form-control" readonly>
            <input type="hidden" name="berat_per_unit" id="berat_per_unit">
            <input type="hidden" name="total_berat" id="total_berat">
        </div>

        {{-- Kendaraan --}}
        <div class="mb-3">
            <label for="kendaraan_id" class="form-label">Pilih Kendaraan</label>
            <select name="kendaraan_id" id="kendaraan_id" class="form-select" required>
                <option value="">-- Pilih Kendaraan --</option>
                @foreach($kendaraans as $kendaraan)
                    <option
                        value="{{ $kendaraan->id }}"
                        data-plat="{{ $kendaraan->plat_nomor }}"
                        data-ukuran="{{ $kendaraan->ukuran }}"
                        data-kapasitas="{{ $kendaraan->kapasitas }}">
                        {{ $kendaraan->nama }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="plat_nomor" id="plat_nomor">
            <input type="hidden" name="ukuran_kendaraan" id="ukuran_kendaraan">
            <div class="mt-2">
                <strong>Plat: <span id="plat-display">-</span></strong><br>
                <strong>Ukuran: <span id="ukuran-display">-</span></strong>
            </div>
        </div>

        {{-- Driver --}}
        <div class="mb-3">
            <label for="driver_id" class="form-label">Pilih Driver</label>
            <select name="driver_id" id="driver_id" class="form-select" required>
                <option value="">-- Pilih Driver --</option>
                @foreach($drivers as $driver)
                    <option
                        value="{{ $driver->id }}"
                        data-status="{{ $driver->status ?? 'Tidak diketahui' }}"
                        data-lat="{{ $driver->latitude }}"
                        data-lng="{{ $driver->longitude }}">
                        {{ $driver->nama }}
                    </option>
                @endforeach
            </select>
            <div class="mt-2">
                <strong>Status: <span id="status-driver-display">-</span></strong>
            </div>
        </div>

        {{-- Peta --}}
        <div class="mb-3">
            <label class="form-label">Pilih Titik Tujuan Pengiriman</label>
            <div id="map" style="height: 500px;" class="mb-4"></div>
        </div>

        {{-- Input koordinat dan estimasi --}}
        <input type="hidden" name="latitude_awal" id="latitude_awal" required>
        <input type="hidden" name="longitude_awal" id="longitude_awal" required>
        <input type="hidden" name="latitude_tujuan" id="latitude_tujuan" required>
        <input type="hidden" name="longitude_tujuan" id="longitude_tujuan" required>
        <input type="hidden" name="estimasi_jarak" id="estimasi_jarak" required>
        <input type="hidden" name="estimasi_waktu" id="estimasi_waktu" required>

        <div id="routing-info" class="p-3 bg-light border rounded mb-3 d-none">
            <strong>Estimasi Jarak:</strong> <span id="jarak-text">-</span> km<br>
            <strong>Estimasi Waktu:</strong> <span id="waktu-text">-</span> menit
        </div>

        {{-- Tujuan (nama tempat, bukan koordinat) --}}
        <div class="mb-3">
            <label for="tujuan" class="form-label">Nama Tujuan</label>
            <input type="text" name="tujuan" id="tujuan" class="form-control" required placeholder="Contoh: Gudang Jakarta Selatan">
        </div>

{{-- Jadwal Kirim --}}
<div class="mb-3">
    <label for="jadwal_kirim" class="form-label">Jadwal Kirim</label>
    <input type="datetime-local" name="jadwal_kirim" id="jadwal_kirim" class="form-control" required>
</div>

        <button type="submit" class="btn btn-primary">Buat Pengiriman</button>
        <a href="{{ route('pengiriman.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection

@push('scripts')
{{-- Leaflet CSS and JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

{{-- Leaflet Routing Machine --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

<script>
    /**
     * Displays a temporary message in the message box.
     * @param {string} message The message to display.
     * @param {string} type The alert type (e.g., 'success', 'warning', 'danger').
     */
    function showMessage(message, type = 'warning') {
        const msgBox = document.getElementById('message-box');
        msgBox.textContent = message;
        msgBox.className = `alert alert-${type}`;
        msgBox.classList.remove('d-none');
        // Hide the message after 5 seconds
        setTimeout(() => {
            msgBox.classList.add('d-none');
        }, 5000);
    }

/**
     * Mengisi input 'tujuan' berdasarkan koordinat menggunakan Nominatim Reverse Geocoding
     * @param {number} lat 
     * @param {number} lng 
     */
    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('tujuan').value = data.display_name;
                } else {
                    document.getElementById('tujuan').value = 'Alamat tidak ditemukan';
                }
            })
            .catch(error => {
                console.error('Error reverse geocoding:', error);
                document.getElementById('tujuan').value = 'Gagal mengambil alamat';
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // --- Product & Weight Calculation Logic ---
        const produkSelect = document.getElementById('produk_id');
        const jumlahInput = document.getElementById('jumlah');
        const beratDisplay = document.getElementById('berat-display');
        const stokDisplay = document.getElementById('stok-display');
        const jenisDisplay = document.getElementById('jenis-display');
        const beratPerUnitInput = document.getElementById('berat_per_unit');
        const totalBeratInput = document.getElementById('total_berat');
        const totalBeratDisplay = document.getElementById('total_berat_display');

        /**
         * Updates the displayed product information and total weight.
         */
        function updateTotalBerat() {
            const jumlah = parseInt(jumlahInput.value) || 0;
            const beratKg = parseFloat(beratPerUnitInput.value) || 0;
            const total = jumlah * beratKg;
            totalBeratDisplay.value = total.toFixed(2);
            totalBeratInput.value = total.toFixed(2);
        }

        produkSelect.addEventListener('change', () => {
            const opt = produkSelect.selectedOptions[0];
            const berat = parseFloat(opt.dataset.berat || 0);
            const skala = opt.dataset.skala;
            const stok = opt.dataset.stok;
            const jenis = opt.dataset.jenis;

            // Convert weight to kg if original unit is grams
            const beratKg = skala === 'g' ? berat / 1000 : berat;

            beratDisplay.textContent = `${berat} ${skala}`;
            stokDisplay.textContent = stok ?? '-';
            jenisDisplay.textContent = jenis ?? '-';
            beratPerUnitInput.value = beratKg;

            updateTotalBerat(); // Recalculate total weight based on new product
        });

        jumlahInput.addEventListener('input', updateTotalBerat); // Recalculate on quantity change

        // --- Vehicle Information Logic ---
        const kendaraanSelect = document.getElementById('kendaraan_id');
        kendaraanSelect.addEventListener('change', () => {
            const opt = kendaraanSelect.selectedOptions[0];
            document.getElementById('plat_nomor').value = opt.dataset.plat ?? '';
            document.getElementById('ukuran_kendaraan').value = opt.dataset.ukuran ?? '';
            document.getElementById('plat-display').textContent = opt.dataset.plat ?? '-';
            document.getElementById('ukuran-display').textContent = opt.dataset.ukuran ?? '-';
        });

        // --- Map and Driver/Destination Logic ---
        const driverSelect = document.getElementById('driver_id');
        const statusDisplay = document.getElementById('status-driver-display');

        const latitudeAwalInput = document.getElementById('latitude_awal');
        const longitudeAwalInput = document.getElementById('longitude_awal');
        const latitudeTujuanInput = document.getElementById('latitude_tujuan');
        const longitudeTujuanInput = document.getElementById('longitude_tujuan');
        const estimasiJarakInput = document.getElementById('estimasi_jarak');
        const estimasiWaktuInput = document.getElementById('estimasi_waktu');
        const jarakText = document.getElementById('jarak-text');
        const waktuText = document.getElementById('waktu-text');
        const routingInfoDiv = document.getElementById('routing-info');

        // Mengambil nilai $pusatLat dan $pusatLng dari Blade
        const pusatLat = {{ $pusatLat }};
        const pusatLng = {{ $pusatLng }};
        const lokasiPusat = L.latLng(pusatLat, pusatLng);

        // Initialize Leaflet map
        const map = L.map('map').setView(lokasiPusat, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);

        let driverMarker = null;        // Stores the Leaflet marker for the driver's location
        let pusatMarker = null;         // New: Stores the Leaflet marker for the central location
        let destinationMarker = null;   // Stores the Leaflet marker for the destination
        let routingControl = null;      // Stores the L.Routing.control instance

        // Inisialisasi marker pusat
        pusatMarker = L.marker([lokasiPusat.lat, lokasiPusat.lng]).addTo(map).bindPopup("Lokasi Pusat").openPopup();

        // Inisialisasi marker tujuan dengan lokasi pusat (default) atau lokasi tujuan yang sudah ada
        const initialTujuanLat = parseFloat(latitudeTujuanInput.value) || lokasiPusat.lat;
        const initialTujuanLng = parseFloat(longitudeTujuanInput.value) || lokasiPusat.lng;

        destinationMarker = L.marker([initialTujuanLat, initialTujuanLng], {
            draggable: true
        }).addTo(map).bindPopup("Geser ke lokasi tujuan").openPopup();

        // Update form inputs and trigger route generation when destination marker is dragged
        destinationMarker.on('dragend', function (e) {
        const pos = destinationMarker.getLatLng();
        latitudeTujuanInput.value = pos.lat;
        longitudeTujuanInput.value = pos.lng;
        reverseGeocode(pos.lat, pos.lng); // ✅ Tambahan di sini
        generateRoute();
    });


        /**
         * Generates and displays the route using Leaflet Routing Machine.
         */
        function generateRoute() {
            const lat1 = parseFloat(latitudeAwalInput.value);
            const lon1 = parseFloat(longitudeAwalInput.value);
            const lat2 = parseFloat(latitudeTujuanInput.value);
            const lon2 = parseFloat(longitudeTujuanInput.value);

            // Clear previous routing control
            if (routingControl) {
                map.removeControl(routingControl);
                routingControl = null;
            }

            // Hide routing info if origin or destination is invalid
            if (isNaN(lat1) || isNaN(lon1) || isNaN(lat2) || isNaN(lon2)) {
                routingInfoDiv.classList.add('d-none');
                return;
            }

            // Define waypoints (driver location -> center -> destination)
            const waypoints = [
                L.latLng(lat1, lon1), // Lokasi Driver
                lokasiPusat,         // Lokasi Pusat
                L.latLng(lat2, lon2) // Lokasi Tujuan
            ];

            // Create new routing control
            routingControl = L.Routing.control({
                waypoints: waypoints,
                routeWhileDragging: false,
                createMarker: function(i, waypoint, n) {
                    // Hanya membuat marker untuk start (driver) dan end (tujuan) secara kustom
                    // Marker tengah (pusat) akan dihandle oleh Leaflet Routing Machine jika tidak ada kustomisasi
                    if (i === 0) { // Driver marker
                        if (driverMarker) map.removeLayer(driverMarker);
                        driverMarker = L.marker(waypoint.latLng).addTo(map).bindPopup("Lokasi Driver").openPopup();
                        return driverMarker;
                    } else if (i === n - 1) { // Destination marker
                        if (destinationMarker) map.removeLayer(destinationMarker);
                        destinationMarker = L.marker(waypoint.latLng, { draggable: true }).addTo(map).bindPopup("Titik Tujuan").openPopup();
                        // Re-attach dragend listener to the new destination marker
                        destinationMarker.on('dragend', function (e) {
                        const pos = destinationMarker.getLatLng();
                        latitudeTujuanInput.value = pos.lat;
                        longitudeTujuanInput.value = pos.lng;
                        reverseGeocode(pos.lat, pos.lng); // ✅ Tambahan di sini juga
                        generateRoute();
                });

                        return destinationMarker;
                    }
                    return null; // Untuk waypoint lokasi pusat, gunakan marker kustom yang sudah dibuat di luar routing control
                },
            }).addTo(map);

            // Listen for route calculation results
            routingControl.on('routesfound', function(e) {
                const routes = e.routes;
                if (routes.length > 0) {
                    const summary = routes[0].summary;
                    const distanceKm = summary.totalDistance / 1000; // meters to km
                    const timeMin = summary.totalTime / 60; // seconds to minutes

                    estimasiJarakInput.value = distanceKm.toFixed(2);
                    estimasiWaktuInput.value = timeMin.toFixed(1);
                    jarakText.textContent = distanceKm.toFixed(2);
                    waktuText.textContent = timeMin.toFixed(1);
                    routingInfoDiv.classList.remove('d-none'); // Show routing info

                    // Fit map bounds to the route
                    map.fitBounds(routes[0].coordinates);
                } else {
                    routingInfoDiv.classList.add('d-none');
                    showMessage('Tidak dapat menemukan rute antara lokasi driver, pusat, dan tujuan.', 'danger');
                }
            });

            routingControl.on('routingerror', function(e) {
                routingInfoDiv.classList.add('d-none');
                showMessage('Terjadi kesalahan saat menghitung rute: ' + e.error.message, 'danger');
            });
        }

        // Event listener for Driver selection change
        driverSelect.addEventListener('change', () => {
            const selected = driverSelect.selectedOptions[0];
            statusDisplay.textContent = selected?.dataset.status || '-';

            const lat = parseFloat(selected?.dataset.lat);
            const lng = parseFloat(selected?.dataset.lng);

            if (!isNaN(lat) && !isNaN(lng)) {
                latitudeAwalInput.value = lat;
                longitudeAwalInput.value = lng;

                // Always try to generate a route when driver changes,
                // as long as destination is also set.
                if (latitudeTujuanInput.value && longitudeTujuanInput.value) {
                    generateRoute();
                } else {
                    // If only driver is selected, pan map to driver's location and set driver marker
                    map.setView([lat, lng], 13);
                    if (driverMarker) map.removeLayer(driverMarker);
                    driverMarker = L.marker([lat, lng]).addTo(map).bindPopup("Lokasi Driver").openPopup();
                }
            } else {
                // If driver has no valid coordinates, clear initial lat/lng inputs
                latitudeAwalInput.value = '';
                longitudeAwalInput.value = '';
                routingInfoDiv.classList.add('d-none'); // Hide routing info if driver location is unknown
                showMessage('Lokasi driver tidak tersedia atau tidak valid. Silakan pilih driver dengan lokasi yang valid.', 'warning');
                // Remove driver marker if it exists
                if (driverMarker) {
                    map.removeLayer(driverMarker);
                    driverMarker = null;
                }
                // Clear routing control if driver becomes invalid
                if (routingControl) {
                    map.removeControl(routingControl);
                    routingControl = null;
                }
            }
        });

        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const awalLat = document.getElementById('latitude_awal').value;
            const tujuanLat = document.getElementById('latitude_tujuan').value;

            // Prevent form submission if driver or destination points are not selected/valid
            if (!awalLat || !tujuanLat || isNaN(parseFloat(awalLat)) || isNaN(parseFloat(tujuanLat))) {
                e.preventDefault();
                showMessage('Silakan pilih driver dan tentukan titik tujuan di peta sebelum mengirim.', 'danger');
            }
        });

        // Initial dispatches to populate displays and map if values are pre-filled (e.g., on form reload due to validation errors)
        produkSelect.dispatchEvent(new Event('change'));
        kendaraanSelect.dispatchEvent(new Event('change'));
        
        // Trigger initial route generation if both driver and destination data are already present (e.g., after a validation error redirect)
        // We'll rely on the driverSelect change to trigger the first route generation if driver data exists.
        // If there's pre-filled destination data, the driverSelect change will also trigger a route.
        driverSelect.dispatchEvent(new Event('change'));

        // If the form reloads with pre-filled destination coordinates (e.g., validation error),
        // ensure the destination marker is correctly positioned and trigger route if possible.
        if (latitudeTujuanInput.value && longitudeTujuanInput.value) {
        const prefilledLat = parseFloat(latitudeTujuanInput.value);
        const prefilledLng = parseFloat(longitudeTujuanInput.value);
        if (!isNaN(prefilledLat) && !isNaN(prefilledLng)) {
            destinationMarker.setLatLng([prefilledLat, prefilledLng]);
            reverseGeocode(prefilledLat, prefilledLng); // ✅ Tambahan
            if (latitudeAwalInput.value && longitudeAwalInput.value) {
                generateRoute();
        }
    }
}
    });
</script>
@endpush