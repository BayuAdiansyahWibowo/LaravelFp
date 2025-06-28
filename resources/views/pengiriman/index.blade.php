@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Pengiriman</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Pencarian --}}
    <form method="GET" action="{{ route('pengiriman.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama produk atau driver...">
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

    <a href="{{ route('pengiriman.create') }}" class="btn btn-success mb-3">Tambah Pengiriman</a>

    @if($pengirimans->count())
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nomor Resi</th>
                    <th>Produk</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Total Berat (kg)</th>
                    <th>Kendaraan</th>
                    <th>Driver</th>
                    <th>Tujuan</th>
                    <th>Jadwal Kirim</th>
                    <th>Estimasi Jarak (km)</th>
                    <th>Estimasi Waktu (menit)</th>
                    <th>Status</th>
                    <th>Diambil Pada</th>
                    <th>Selesai Pada</th>
                    <th>Bukti</th> {{-- ✅ Tambahan kolom bukti --}}
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengirimans as $pengiriman)
                    <tr>
                        <td>{{ $pengiriman->id }}</td>
                        <td>{{ $pengiriman->nomor_resi }}</td>
                        <td>{{ $pengiriman->produk->nama ?? '-' }}</td>
                        <td>{{ $pengiriman->jenis }}</td>
                        <td>{{ $pengiriman->jumlah }}</td>
                        <td>{{ number_format($pengiriman->total_berat, 2) }}</td>
                        <td>{{ $pengiriman->plat_nomor }} ({{ $pengiriman->ukuran_kendaraan }})</td>
                        <td>{{ $pengiriman->driver->nama ?? '-' }}</td>
                        <td>{{ $pengiriman->tujuan ?? '-' }}</td>
                        <td>
                            @if($pengiriman->jadwal_kirim)
                                {{ \Carbon\Carbon::parse($pengiriman->jadwal_kirim)->format('d-m-Y H:i') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $pengiriman->estimasi_jarak }} km</td>
                        <td>{{ $pengiriman->estimasi_waktu }} menit</td>
                        <td>
                            @switch($pengiriman->status)
                    @case('menunggu_konfirmasi')
                        <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>
                        @break
                    @case('driver_menjemput_barang')
                        <span class="badge bg-info text-dark">Menjemput Barang</span>
                        @break
                    @case('sedang_pengiriman')
                        <span class="badge bg-primary">Sedang Pengiriman</span>
                        @break
                    @case('dikirim')
                        <span class="badge bg-primary">Dikirim</span>
                        @break
                    @case('selesai')
                        <span class="badge bg-success">Selesai</span>
                        @break
                    @case('ditolak')
                        <span class="badge bg-danger">Ditolak</span>
                        @break
                    @default
                        <span class="badge bg-secondary">{{ ucfirst($pengiriman->status) }}</span>
                @endswitch

                        </td>
                        <td>
                            @if($pengiriman->diambil_at)
                                <span class="badge bg-info text-dark">
                                    {{ \Carbon\Carbon::parse($pengiriman->diambil_at)->format('d-m-Y H:i') }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted">Belum Diambil</span>
                            @endif
                        </td>
                        <td>
                            @if($pengiriman->status === 'ditolak_oleh_driver')
                                <span class="badge bg-danger">Ditolak ({{ \Carbon\Carbon::parse($pengiriman->selesai_at)->format('d-m-Y H:i') }})</span>
                            @elseif($pengiriman->selesai_at)
                                <span class="badge bg-success">
                                    {{ \Carbon\Carbon::parse($pengiriman->selesai_at)->format('d-m-Y H:i') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">Belum Selesai</span>
                            @endif
                        </td>
                        <td>
                            @if($pengiriman->bukti_pengiriman)
                                <a href="{{ asset('storage/' . $pengiriman->bukti_pengiriman) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $pengiriman->bukti_pengiriman) }}" width="60" alt="Bukti">
                                </a>
                            @else
                                <span class="text-muted">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('monitoring.index', ['pengiriman_id' => $pengiriman->id]) }}" class="btn btn-info btn-sm">Lihat</a>
                            <a href="{{ route('pengiriman.edit', $pengiriman->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('pengiriman.destroy', $pengiriman->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada data pengiriman.</p>
    @endif
</div>
@endsection
