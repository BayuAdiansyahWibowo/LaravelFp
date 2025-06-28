@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Manajemen Kendaraan</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('kendaraan.create') }}" class="btn btn-primary mb-3">+ Tambah Kendaraan</a>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('kendaraan.index') }}" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari nama kendaraan..." value="{{ $filter_search ?? '' }}">
        </div>
        <div class="col-md-3">
            <select name="ukuran" class="form-select">
                <option value="">-- Semua Ukuran --</option>
                <option value="kecil" {{ ($filter_ukuran ?? '') == 'kecil' ? 'selected' : '' }}>Kecil</option>
                <option value="sedang" {{ ($filter_ukuran ?? '') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="besar" {{ ($filter_ukuran ?? '') == 'besar' ? 'selected' : '' }}>Besar</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-outline-primary">Terapkan</button>
            <a href="{{ route('kendaraan.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    {{-- Tabel Kendaraan --}}
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Nama</th>
                <th>Plat Nomor</th>
                <th>Ukuran</th>
                <th>Kapasitas (kg)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kendaraans as $item)
                <tr>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->plat_nomor }}</td>
                    <td>{{ ucfirst($item->ukuran) }}</td>
                    <td>{{ $item->kapasitas }}</td>
                    <td>
                        <a href="{{ route('kendaraan.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('kendaraan.destroy', $item->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus kendaraan ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada data kendaraan ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
