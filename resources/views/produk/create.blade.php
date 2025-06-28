@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Tambah Produk</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('produk.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Produk</label>
            <input type="text" name="nama" class="form-control" id="nama" required>
        </div>
        <div class="mb-3">
            <label for="jenis" class="form-label">Jenis Produk</label>
            <select name="jenis" class="form-select" id="jenis" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="pangan">Pangan</option>
                <option value="elektronik">Elektronik</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="stok" class="form-label">Stok</label>
            <input type="number" name="stok" class="form-control" id="stok" min="0" required>
        </div>
        <div class="mb-3">
            <label for="berat_per_unit" class="form-label">Berat per Unit</label>
            <div class="input-group">
                <input type="number" step="0.01" name="berat_per_unit" class="form-control" id="berat_per_unit" min="0" required>
                <select name="skala_berat" id="skala_berat" class="form-select" style="max-width: 100px;" required>
                    <option value="kg">kg</option>
                    <option value="g">gram</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Total Berat (kg)</label>
            <input type="text" class="form-control" id="total_berat" readonly>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<script>
    const stokInput = document.getElementById('stok');
    const beratInput = document.getElementById('berat_per_unit');
    const skalaInput = document.getElementById('skala_berat');
    const totalBeratOutput = document.getElementById('total_berat');

    function updateTotalBerat() {
        const stok = parseFloat(stokInput.value) || 0;
        let berat = parseFloat(beratInput.value) || 0;
        const skala = skalaInput.value;

        if (skala === 'g') {
            berat = berat / 1000;
        }

        const totalBerat = stok * berat;
        totalBeratOutput.value = totalBerat.toFixed(2);
    }

    stokInput.addEventListener('input', updateTotalBerat);
    beratInput.addEventListener('input', updateTotalBerat);
    skalaInput.addEventListener('change', updateTotalBerat);

    updateTotalBerat();
</script>
@endsection
