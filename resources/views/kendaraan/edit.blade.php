@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Kendaraan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('kendaraan.update', $kendaraan->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label>Nama Kendaraan</label>
            <input type="text" name="nama" value="{{ old('nama', $kendaraan->nama) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Plat Nomor</label>
            <input type="text" name="plat_nomor" value="{{ old('plat_nomor', $kendaraan->plat_nomor) }}" class="form-control" required placeholder="Contoh: B 1234 XYZ">
        </div>

        <div class="mb-3">
            <label>Ukuran</label>
            <select name="ukuran" class="form-control" required>
                <option value="">-- Pilih Ukuran --</option>
                <option value="kecil" {{ $kendaraan->ukuran == 'kecil' ? 'selected' : '' }}>Kecil</option>
                <option value="sedang" {{ $kendaraan->ukuran == 'sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="besar" {{ $kendaraan->ukuran == 'besar' ? 'selected' : '' }}>Besar</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Kapasitas (kg)</label>
            <input type="number" name="kapasitas" value="{{ old('kapasitas', $kendaraan->kapasitas) }}" class="form-control" min="0" required>
        </div>

        <button class="btn btn-primary">Update</button>
        <a href="{{ route('kendaraan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
