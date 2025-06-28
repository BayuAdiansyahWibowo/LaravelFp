<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Tracking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }

        .wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            transition: all 0.3s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar.hidden {
            width: 0;
            padding: 0 !important;
        }

        .sidebar.hidden .sidebar-content {
            display: none !important;
        }

        .sidebar .nav-link {
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
            color: white;
        }

        .nav-link.active {
            background-color: #0d6efd;
            font-weight: bold;
        }

        .content {
            flex: 1;
            padding: 1.5rem;
            transition: margin-left 0.3s ease;
        }

        .topbar {
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: #0d6efd;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .sidebar-footer {
            text-align: center;
            font-size: 0.8rem;
            color: #ccc;
            padding: 15px;
        }

        .sidebar h4 {
            font-size: 1.25rem;
            margin-bottom: 20px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .dark-mode {
            background-color: #121212;
            color: #f1f1f1;
        }

        .dark-mode .sidebar {
            background-color: #1e1e1e;
        }

        .dark-mode .sidebar .nav-link:hover {
            background-color: #333;
        }

        .dark-mode .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar p-3">
        <div class="sidebar-content flex-grow-1 d-flex flex-column">
            <div class="profile">
                <img src="https://ui-avatars.com/api/?name=Admin" alt="Admin Avatar">
                <div>
                    <strong>{{ auth()->user()->name }}</strong><br>
                    <small>{{ auth()->user()->email }}</small>
                </div>
            </div>

            <h4><i class="bi bi-globe2 me-2"></i>Tracking</h4>
            <ul class="nav flex-column mt-2">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('produk.index') }}" class="nav-link {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-2"></i> Manajemen Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kendaraan.index') }}" class="nav-link {{ request()->routeIs('kendaraan.*') ? 'active' : '' }}">
                        <i class="bi bi-truck-front me-2"></i> Kendaraan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('driver.index') }}" class="nav-link {{ request()->routeIs('driver.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge me-2"></i> Driver
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('monitoring.index') }}" class="nav-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                        <i class="bi bi-geo-alt me-2"></i> Monitoring
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pengiriman.index') }}" class="nav-link {{ request()->routeIs('pengiriman.*') ? 'active' : '' }}">
                        <i class="bi bi-send-check me-2"></i> Tugas Pengiriman
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-data me-2"></i> Laporan
                    </a>
                </li>
            </ul>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>

        <div class="sidebar-footer mt-auto">
            &copy; {{ date('Y') }} Tracking Admin
        </div>
    </div>

    <!-- Main Content -->
    <div id="mainContent" class="content">
        <!-- Topbar -->
        <div class="topbar mb-3">
            <div>
                <button id="toggleSidebar" class="toggle-btn" title="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

        @yield('content')
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const darkModeToggle = document.getElementById('darkModeToggle');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('hidden');
        setTimeout(() => {
            if (window.myMap && typeof window.myMap.invalidateSize === 'function') {
                window.myMap.invalidateSize();
            }
        }, 300);
    });

    darkModeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
    });
</script>

@yield('scripts')
@stack('scripts')

</body>
</html>
