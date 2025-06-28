@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Laporan Pengiriman Selesai</h3>

    {{-- FILTER DRIVER --}}
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="driver" class="form-label">Pilih Driver</label>
            <select name="driver_id" id="driver" class="form-control">
                <option value="">-- Semua Driver --</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                        {{ $driver->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- TABEL LAPORAN --}}
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nomor Resi</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Total Berat (kg)</th>
                <th>Tujuan</th>
                <th>Driver</th>
                <th>Jadwal Kirim</th>
                <th>Diambil Pada</th>
                <th>Selesai Pada</th>
                <th>Bukti</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengirimans as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->nomor_resi }}</td>
                    <td>{{ $p->produk->nama ?? '-' }}</td>
                    <td>{{ $p->jumlah }}</td>
                    <td>{{ $p->total_berat }}</td>
                    <td>{{ $p->tujuan }}</td>
                    <td>{{ $p->driver->nama ?? '-' }}</td>
                    <td>{{ $p->jadwal_kirim }}</td>
                    <td>{{ $p->diambil_at }}</td>
                    <td>{{ $p->selesai_at }}</td>
                    <td>
                        @if ($p->bukti_pengiriman)
                            <a href="{{ asset('storage/' . $p->bukti_pengiriman) }}" target="_blank">Lihat</a>
                        @else
                            Belum ada
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">Belum ada laporan pengiriman selesai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
