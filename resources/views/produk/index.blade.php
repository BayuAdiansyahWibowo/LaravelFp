@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Daftar Produk</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <select name="jenis" class="form-select">
                <option value="">Semua Jenis</option>
                <option value="pangan" {{ request('jenis') == 'pangan' ? 'selected' : '' }}>Pangan</option>
                <option value="elektronik" {{ request('jenis') == 'elektronik' ? 'selected' : '' }}>Elektronik</option>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Filter</button>
        </form>

        <a href="{{ route('produk.create') }}" class="btn btn-success">+ Tambah Produk</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Stok</th>
                <th>Berat/Unit</th>
                <th>Total Berat (kg)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produks as $index => $produk)
                @php
                    $beratPerUnitKg = $produk->skala_berat === 'g' 
                        ? $produk->berat_per_unit / 1000 
                        : $produk->berat_per_unit;

                    $totalBeratKg = $produk->stok * $beratPerUnitKg;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $produk->nama }}</td>
                    <td>{{ ucfirst($produk->jenis) }}</td>
                    <td>{{ $produk->stok }}</td>
                    <td>{{ $produk->berat_per_unit }} {{ $produk->skala_berat }}</td>
                    <td>{{ number_format($totalBeratKg, 2) }} kg</td>
                    <td>
                        <a href="{{ route('produk.edit', $produk->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('produk.destroy', $produk->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada produk ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
