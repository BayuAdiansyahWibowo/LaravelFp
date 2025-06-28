@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Driver</h2>

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

    <form action="{{ route('driver.update', $driver->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT') {{-- Digunakan untuk spoofing metode PUT --}}

        <div class="mb-3">
            <label>Nama Driver</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $driver->nama) }}" required>
        </div>

        <div class="mb-3">
            <label>Nomor Telepon</label>
            <input type="text" name="nomor_telepon" class="form-control" value="{{ old('nomor_telepon', $driver->nomor_telepon) }}" required>
        </div>

        <div class="mb-3">
            <label>Upload Gambar SIM (kosongkan jika tidak ingin mengubah)</label>
            <input type="file" name="sim" class="form-control">
            @if ($driver->sim_path)
                <small class="form-text text-muted">Gambar SIM saat ini: <a href="{{ asset('storage/' . $driver->sim_path) }}" target="_blank">Lihat SIM</a></small>
            @endif
        </div>

        <div class="mb-3">
            <label>Alamat Driver</label>
            <textarea name="alamat" class="form-control" required>{{ old('alamat', $driver->alamat) }}</textarea>
        </div>


        <button type="submit" class="btn btn-primary">Perbarui</button>
    </form>
</div>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

{{-- Leaflet Geocoder (search) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
    const initialLat = {{ old('latitude', $driver->latitude) ?? -6.200000 }};
    const initialLng = {{ old('longitude', $driver->longitude) ?? 106.816666 }};

    const map = L.map('map').setView([initialLat, initialLng], 13);

    // Load tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Tambahkan marker draggable
    const marker = L.marker([initialLat, initialLng], {
        draggable: true
    }).addTo(map);

    // Update hidden input saat marker dipindah
    marker.on('dragend', function(e) {
        const latlng = marker.getLatLng();
        document.getElementById('latitude').value = latlng.lat;
        document.getElementById('longitude').value = latlng.lng;
    });

    // Geolocation dari browser (hanya jika lokasi awal adalah default)
    if (initialLat === -6.200000 && initialLng === 106.816666 && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }, function() {
            // Jika gagal, tetap gunakan nilai awal yang ada di hidden field
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
