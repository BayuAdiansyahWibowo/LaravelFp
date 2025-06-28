@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Dashboard</h1>
        <span class="badge bg-info fs-6 text-dark shadow-sm">
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <div class="row" id="menuGrid">
        <!-- Menu cards will be generated here -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    const fiturList = [
        {
    title: "Manajemen Produk",
    route: "{{ route('produk.index') }}",
    icon: "📦",
    bg: "bg-primary"
},
{
    title: "Kendaraan",
    route: "{{ route('kendaraan.index') }}",
    icon: "🚚",
    bg: "bg-success"
},
{
    title: "Driver",
    route: "{{ route('driver.index') }}",
    icon: "🧑‍✈️",
    bg: "bg-warning text-dark"
},
{
    title: "Monitoring",
    route: "{{ route('monitoring.index') }}",
    icon: "📡",
    bg: "bg-danger"
},
{
    title: "Tugas Pengiriman",
    route: "{{ route('pengiriman.index') }}",
    icon: "🗺️",
    bg: "bg-secondary"
},
{
    title: "Laporan",
    route: "{{ route('laporan.index') }}",
    icon: "📄",
    bg: "bg-dark"
}

    ];

    const menuGrid = document.getElementById('menuGrid');

    fiturList.forEach(fitur => {
        const col = document.createElement('div');
        col.className = "col-md-4 mb-4";

        col.innerHTML = `
            <div class="card text-white ${fitur.bg} shadow-sm h-100 border-0 rounded-4 hover-shadow" 
                 style="cursor: pointer;" 
                 onclick="window.location.href='${fitur.route}'">
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                    <div style="font-size: 3rem;">${fitur.icon}</div>
                    <h5 class="card-title mt-3">${fitur.title}</h5>
                </div>
            </div>
        `;

        menuGrid.appendChild(col);
    });
</script>

<style>
    .hover-shadow:hover {
        box-shadow: 0 0 20px rgba(0,0,0,0.2) !important;
        transform: scale(1.03);
        transition: all 0.2s ease-in-out;
    }
</style>
@endpush
