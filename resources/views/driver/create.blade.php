@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Driver</h2>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('driver.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Nama Driver</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        {{-- Input Nomor Telepon baru --}}
        <div class="mb-3">
            <label>Nomor Telepon</label>
            <input type="text" name="nomor_telepon" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Upload Gambar SIM</label>
            <input type="file" name="sim" class="form-control">
        </div>

        <div class="mb-3">
            <label>Alamat Driver</label>
            <textarea name="alamat" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label>Pilih Lokasi Driver</label>
            <div id="map" style="height: 300px;"></div>
        </div>

        {{-- Hidden fields lokasi --}}
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <div class="mb-3">
            <label>Status Driver</label>
            <select name="status" class="form-select" required>
                <option value="">-- Pilih Status --</option>
                <option value="tersedia">Tersedia</option>
                <option value="sedang_pengiriman">Sedang Pengiriman</option>
                <option value="tidak_aktif">Tidak Aktif</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

{{-- Leaflet Geocoder (search) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
    const defaultLat = -6.200000;
    const defaultLng = 106.816666;

    const map = L.map('map').setView([defaultLat, defaultLng], 13);

    // Load tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Tambahkan marker draggable
    const marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    // Update hidden input saat marker dipindah
    marker.on('dragend', function(e) {
        const latlng = marker.getLatLng();
        document.getElementById('latitude').value = latlng.lat;
        document.getElementById('longitude').value = latlng.lng;
    });

    // Geolocation dari browser
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }, function() {
            // Jika gagal, pakai default
            document.getElementById('latitude').value = defaultLat;
            document.getElementById('longitude').value = defaultLng;
        });
    }

    // Tambahkan kontrol pencarian alamat
    L.Control.geocoder({
        defaultMarkGeocode: false
    })
    .on('markgeocode', function(e) {
        const latlng = e.geocode.center;
        map.setView(latlng, 16);
        marker.setLatLng(latlng);
        document.getElementById('latitude').value = latlng.lat;
        document.getElementById('longitude').value = latlng.lng;
    })
    .addTo(map);
</script>
@endsection
