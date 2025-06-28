@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Driver</h2>

    <a href="{{ route('driver.create') }}" class="btn btn-primary mb-3">+ Tambah Driver</a>

    {{-- Filter --}}
    <form method="GET" class="mb-4 row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari nama..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="sedang_pengiriman" {{ request('status') == 'sedang_pengiriman' ? 'selected' : '' }}>Sedang Pengiriman</option>
                <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-secondary w-100">Filter</button>
            <a href="{{ route('driver.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Nomor Telepon</th>
                <th>SIM</th>
                <th>Alamat</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tbody id="driver-table-body">
    @include('driver._table', ['drivers' => $drivers])
</tbody>

        </tbody>
    </table>

    <!-- Modal SIM -->
    <div class="modal fade" id="simModal" tabindex="-1" aria-labelledby="simModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-body text-center">
                    <img id="simModalImg" src="" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showSim(src) {
        document.getElementById('simModalImg').src = src;
    }

    function refreshDriverTable() {
        fetch('{{ route("driver.refresh") }}')
            .then(res => res.text())
            .then(html => {
                document.getElementById('driver-table-body').innerHTML = html;
            });
    }

    setInterval(refreshDriverTable, 5000); // setiap 5 detik
</script>
@endsection

