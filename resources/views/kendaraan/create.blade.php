@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Kendaraan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('kendaraan.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Nama Kendaraan</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Plat Nomor</label>
            <input type="text" name="plat_nomor" class="form-control" required placeholder="Contoh: B 1234 XYZ">
        </div>

        <div class="mb-3">
            <label>Ukuran</label>
            <select name="ukuran" class="form-control" required>
                <option value="">-- Pilih Ukuran --</option>
                <option value="kecil">Kecil</option>
                <option value="sedang">Sedang</option>
                <option value="besar">Besar</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Kapasitas (kg)</label>
            <input type="number" name="kapasitas" class="form-control" min="0" required>
        </div>

        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('kendaraan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
