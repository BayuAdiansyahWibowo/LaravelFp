@forelse($drivers as $driver)
    <tr>
        <td>{{ $driver->nama }}</td>
        <td>{{ $driver->nomor_telepon }}</td>
        <td>
            @if($driver->sim_path)
                <img src="{{ asset('storage/' . $driver->sim_path) }}"
                    width="80"
                    style="cursor: zoom-in;"
                    data-bs-toggle="modal"
                    data-bs-target="#simModal"
                    onclick="showSim('{{ asset('storage/' . $driver->sim_path) }}')">
            @else
                Tidak ada
            @endif
        </td>
        <td>{{ $driver->alamat }}</td>
        <td>{{ $driver->latitude }}, {{ $driver->longitude }}</td>
        <td>
            @switch($driver->status)
            @case('tersedia')
                <span class="badge bg-success">Tersedia</span>
                @break
            @case('menjemput_barang')
                <span class="badge bg-info text-dark">Menjemput Barang</span>
                @break
            @case('sedang_pengiriman')
                <span class="badge bg-warning text-dark">Sedang Pengiriman</span>
                @break
            @case('tidak_aktif')
                <span class="badge bg-secondary">Tidak Aktif</span>
                @break
            @default
                <span class="badge bg-light text-dark">-</span>
        @endswitch
        </td>
        <td>
            <a href="{{ route('driver.edit', $driver->id) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('driver.destroy', $driver->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus?')">Hapus</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center">Tidak ada data driver.</td>
    </tr>
@endforelse
