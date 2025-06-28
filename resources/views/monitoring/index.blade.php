@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Monitoring Lokasi Driver & Pengiriman</h1>

    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" id="searchDriver" class="form-control rounded-md" placeholder="Cari nama driver...">
        </div>
        <div class="col-md-6">
            <select id="filterDriver" class="form-control rounded-md">
                <option value="">-- Tampilkan Semua Driver --</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->nama }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Map container with rounded corners --}}
    <div id="map" style="height: 500px; border-radius: 0.5rem; overflow: hidden;"></div>

    <div class="mt-3 row">
        <div class="col-md-12">
            {{-- Route information card --}}
            <div id="route-info" class="card d-none shadow-md rounded-lg">
                <div class="card-body">
                    <h5 class="card-title text-lg font-semibold" id="resi-title"></h5>
                    <p class="text-sm"><strong>Jarak:</strong> <span id="jarak-text"></span></p>
                    <p class="text-sm"><strong>Estimasi waktu:</strong> <span id="waktu-text"></span></p>
                    <hr class="my-3">
                    <h6 class="text-md font-medium">Petunjuk Arah:</h6>
                    <ol id="step-list" class="list-decimal pl-5"></ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
{{-- Leaflet Routing Machine CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<style>
    /* Custom styling for the route info card */
    #route-info {
        max-height: 300px;
        overflow-y: auto;
        /* Using Tailwind-like classes for shadow and rounded corners */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-radius: 0.5rem;
    }
    /* Style for the map container */
    #map {
        border-radius: 0.5rem;
    }
</style>
@endpush

@push('scripts')
{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
{{-- Leaflet Routing Machine JS --}}
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.min.js"></script>
{{-- Pusher and Laravel Echo JS --}}
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.min.js"></script>

<script>
    // Get central location coordinates from Blade
    const pusatLat = {{ $pusatLat }};
    const pusatLng = {{ $pusatLng }};
    const lokasiPusat = L.latLng(pusatLat, pusatLng);

    // Initialize the Leaflet map centered on the central location
    const map = L.map('map').setView(lokasiPusat, 11);

    // Add OpenStreetMap tile layer to the map
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add a marker for the central location
    const pusatMarker = L.marker(lokasiPusat, {
        icon: L.divIcon({
            className: 'custom-div-icon',
            html: '<div style="background-color: blue; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        })
    }).addTo(map).bindPopup("Lokasi Pusat").openPopup();

    // Global data from Blade (converted to JavaScript objects)
    let drivers = JSON.parse('@json($drivers)');
    const pengirimen = JSON.parse('@json($pengirimen)');

    // --- Map Layer Management ---
    let driverMarkers = {}; // Stores L.Marker objects for drivers {driverId: L.Marker}
    // Stores L.Routing.Control instances for static pengiriman routes {pengirimanId: {control: L.Routing.Control, polyline: L.Polyline}}
    let staticPengirimanRouteLayers = {}; 
    let activeDriverRouteControl = null; // Stores L.Routing.Control for the active/filtered driver's route

    /**
     * Shows route details in the info card.
     * @param {object} pengiriman - The pengiriman object.
     * @param {object} routeSummary - Summary object from L.Routing.control.
     * @param {Array<object>} steps - Instructions steps from L.Routing.control.
     */
    function showRouteDetails(pengiriman, routeSummary, steps) {
        document.getElementById('route-info').classList.remove('d-none');
        document.getElementById('resi-title').textContent = `Resi: ${pengiriman.nomor_resi}`;
        document.getElementById('jarak-text').textContent = `${(routeSummary.totalDistance / 1000).toFixed(2)} km`;
        document.getElementById('waktu-text').textContent = `${(routeSummary.totalTime / 60).toFixed(1)} menit`;

        const instructionsList = document.getElementById('step-list');
        instructionsList.innerHTML = ''; // Clear previous steps
        if (steps && steps.length > 0) {
            steps.forEach((step) => {
                const li = document.createElement('li');
                li.textContent = step.text;
                instructionsList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = "Tidak ada petunjuk arah yang tersedia.";
            instructionsList.appendChild(li);
        }
    }

    /**
     * Adds or updates a driver's marker on the map.
     * @param {object} driver - The driver object with latitude and longitude.
     */
    function addOrUpdateDriverMarker(driver) {
    if (isNaN(driver.latitude) || isNaN(driver.longitude)) {
        console.warn(`Driver ID ${driver.id} (${driver.nama}) has invalid coordinates.`);
        return;
    }

    const newLatLng = L.latLng(driver.latitude, driver.longitude);
    const popupContent = `
        <b>Nama Driver:</b> ${driver.nama}<br>
        <b>Kendaraan:</b> ${driver.kendaraan?.nama ?? '-'}
    `;

    if (driverMarkers[driver.id]) {
        const marker = driverMarkers[driver.id];
        const currentLatLng = marker.getLatLng();
        let startTime = null;
        const duration = 500; // dalam milidetik (0.5 detik)

        function animateMarker(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);

            const lat = currentLatLng.lat + (newLatLng.lat - currentLatLng.lat) * progress;
            const lng = currentLatLng.lng + (newLatLng.lng - currentLatLng.lng) * progress;

            marker.setLatLng([lat, lng]);

            if (progress < 1) {
                requestAnimationFrame(animateMarker);
            }
        }

        requestAnimationFrame(animateMarker);
        marker.setPopupContent(popupContent);
    } else {
        // Tambahkan marker baru kalau belum ada
        driverMarkers[driver.id] = L.marker(newLatLng, {
            // Bisa tambahkan icon custom di sini
        }).addTo(map).bindPopup(popupContent);
    }
}


    /**
     * Draws a set of static pengiriman routes from their recorded origin to destination, passing through the central location.
     * These routes are clickable to show details.
     * @param {Array<object>} pengirimenToDraw - An array of pengiriman objects to draw routes for.
     */
    function drawStaticPengirimanRoutes(pengirimenToDraw) {
        // Clear all existing static routes first
        Object.values(staticPengirimanRouteLayers).forEach(layer => {
            if (layer.routingControl) {
                map.removeControl(layer.routingControl);
            }
            if (layer.polyline) {
                map.removeLayer(layer.polyline);
            }
        });
        staticPengirimanRouteLayers = {}; // Reset static route layers

        pengirimenToDraw.forEach(p => {
            // Ensure valid coordinates for the route
            if (isNaN(parseFloat(p.latitude_awal)) || isNaN(parseFloat(p.longitude_awal)) || 
                isNaN(parseFloat(p.latitude_tujuan)) || isNaN(parseFloat(p.longitude_tujuan))) {
                console.warn(`Pengiriman ID ${p.id} (Resi: ${p.nomor_resi}) has invalid route coordinates.`);
                return;
            }

            const start = L.latLng(parseFloat(p.latitude_awal), parseFloat(p.longitude_awal));
            const end = L.latLng(parseFloat(p.latitude_tujuan), parseFloat(p.longitude_tujuan));

            // Create a routing control for the static route
            const routingControl = L.Routing.control({
                waypoints: [start, lokasiPusat, end], // Route through the central location
                routeWhileDragging: false,
                draggableWaypoints: false,
                addWaypoints: false,
                createMarker: (i, waypoint, n) => null, // No default markers from routing control
                lineOptions: {
                    styles: [{ color: 'green', opacity: 0.7, weight: 3 }] // Green for static routes
                },
                show: false // Hide the routing control panel
            });

            routingControl.addTo(map);
            // Store the control for later removal
            staticPengirimanRouteLayers[p.id] = { routingControl: routingControl }; 

            // Add a transparent polyline on top for click detection
            routingControl.on('routesfound', function(e) {
                const route = e.routes[0];
                const clickablePolyline = L.polyline(route.coordinates, {
                    color: 'transparent', // Make it transparent
                    weight: 10,           // Make it thick for easier clicking
                    opacity: 0,
                    interactive: true     // Essential for click events
                }).addTo(map);

                clickablePolyline.on('click', () => {
                    showRouteDetails(p, route.summary, route.instructions);
                });
                staticPengirimanRouteLayers[p.id].polyline = clickablePolyline; // Store polyline reference
            });
        });
    }

    /**
     * Draws or updates the active driver's route from their current location to destination, passing through the central location.
     * This route is more prominent and updates with real-time data.
     * @param {object} driver - The driver object.
     * @param {object} pengiriman - The associated pengiriman object.
     */
    function drawActiveDriverRoute(driver, pengiriman) {
        if (!driver || !pengiriman || 
            isNaN(driver.latitude) || isNaN(driver.longitude) ||
            isNaN(parseFloat(pengiriman.latitude_tujuan)) || isNaN(parseFloat(pengiriman.longitude_tujuan))) {
            return; // Cannot draw route without complete valid data
        }

        // Remove any previously drawn active driver route
        if (activeDriverRouteControl) {
            map.removeControl(activeDriverRouteControl);
        }

        const start = L.latLng(driver.latitude, driver.longitude);
        const end = L.latLng(parseFloat(pengiriman.latitude_tujuan), parseFloat(pengiriman.longitude_tujuan));

        // Define custom icons for start and end markers of the active route
        const driverIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const destinationIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        activeDriverRouteControl = L.Routing.control({
            waypoints: [start, lokasiPusat, end], // Route through the central location
            routeWhileDragging: false,
            draggableWaypoints: false,
            addWaypoints: false,
            createMarker: (i, waypoint, n) => {
                if (i === 0) { // Start marker for driver's current location
                    return L.marker(waypoint.latLng, { icon: driverIcon })
                             .bindPopup(`<b>Driver: ${driver.nama} (Lokasi Sekarang)</b>`);
                } else if (i === n - 1) { // End marker for destination
                    return L.marker(waypoint.latLng, { icon: destinationIcon })
                             .bindPopup(`<b>Tujuan Pengiriman: ${pengiriman.nomor_resi}</b>`);
                }
                return null; // Don't create markers for intermediate waypoints (like pusat)
            },
            lineOptions: {
                styles: [{ color: 'orange', opacity: 0.9, weight: 5 }] // Orange for active routes
            },
            show: false // Hide the routing control panel
        }).addTo(map);

        // Listen for routesfound event to show details for the active route
        activeDriverRouteControl.on('routesfound', function(e) {
            const route = e.routes[0];
            showRouteDetails(pengiriman, route.summary, route.instructions);
            // Fit map bounds to this active route
            map.fitBounds(activeDriverRouteControl.getWaypoints().map(wp => wp.latLng).concat(route.coordinates).getBounds().pad(0.1));
        });

        // Manually trigger route calculation
        activeDriverRouteControl.setWaypoints([start, lokasiPusat, end]); // Ensure waypoints are set again if dynamically added
        map.fitBounds(L.latLngBounds(start, lokasiPusat, end).pad(0.2)); // Adjust map view to show all three points initially
    }

    /**
     * Initializes all map elements (drivers and static pengiriman routes).
     * This is called on page load or when "Show All Drivers" is selected.
     */
    function initAllMapElements() {
        // Clear all existing map elements (except pusatMarker)
        Object.values(driverMarkers).forEach(marker => map.removeLayer(marker));
        driverMarkers = {};

        // Draw all driver markers
        drivers.forEach(addOrUpdateDriverMarker);

        // Draw all static pengiriman routes (for all pengirimen)
        drawStaticPengirimanRoutes(pengirimen);

        // Clear any active orange route that might be left
        if (activeDriverRouteControl) {
            map.removeControl(activeDriverRouteControl);
            activeDriverRouteControl = null;
        }

        // Hide route info card
        document.getElementById('route-info').classList.add('d-none');
    }

    /**
     * Applies filters and updates the map display accordingly.
     */
    function applyFilters() {
        const keyword = document.getElementById('searchDriver').value.toLowerCase();
        const selectedId = document.getElementById('filterDriver').value;

        // 1. Clear existing map elements that might conflict
        //    (active orange route and driver markers)
        if (activeDriverRouteControl) {
            map.removeControl(activeDriverRouteControl);
            activeDriverRouteControl = null;
        }
        document.getElementById('route-info').classList.add('d-none');

        Object.values(driverMarkers).forEach(marker => {
            if (marker !== pusatMarker) {
                map.removeLayer(marker);
            }
        });
        driverMarkers = {}; // Reset the driverMarkers object


        // 2. Determine which drivers and pengiriman to show based on filters
        let driversToShow = [...drivers];
        let pengirimenToShow = [...pengirimen]; // Default: all static routes


        if (selectedId) {
            // If a specific driver is selected:
            driversToShow = driversToShow.filter(driver => driver.id == selectedId);
            pengirimenToShow = pengirimenToShow.filter(p => p.driver_id == selectedId); // Only static routes for this driver
            
            if (driversToShow.length > 0) {
                // Add only the selected driver's marker
                addOrUpdateDriverMarker(driversToShow[0]);
                map.setView([driversToShow[0].latitude, driversToShow[0].longitude], 13);

                // If the selected driver has an active pengiriman, draw their active route (orange)
                if (pengirimenToShow.length > 0) {
                    const activePengiriman = pengirimenToShow.find(p => p.driver_id === driversToShow[0].id);
                    if(activePengiriman) {
                        drawActiveDriverRoute(driversToShow[0], activePengiriman);
                    }
                }
            } else {
                // If selected driver has no data (e.g., deleted or no location), pan to center
                map.setView(lokasiPusat, 11);
            }
        } else {
            // If "Show All Drivers" is selected, driversToShow and pengirimenToShow remain 'all'
            // Handled by initAllMapElements() later.
        }

        // 3. Apply keyword search if present
        if (keyword) {
            driversToShow = driversToShow.filter(driver =>
                driver.nama.toLowerCase().includes(keyword)
            );
            pengirimenToShow = pengirimenToShow.filter(p => {
                const driverForPengiriman = drivers.find(d => d.id === p.driver_id);
                return driverForPengiriman && driverForPengiriman.nama.toLowerCase().includes(keyword);
            });
        }
        
        // 4. Draw markers and static routes based on `driversToShow` and `pengirimenToShow`
        if (!selectedId && !keyword) {
            // This is the 'Show All' default state
            initAllMapElements(); // This function already handles drawing all
        } else {
            // For filtered states (either by ID or by Keyword)
            driversToShow.forEach(addOrUpdateDriverMarker); // Draw filtered driver markers
            drawStaticPengirimanRoutes(pengirimenToShow);   // Draw filtered static routes
        }

        // If a single driver is chosen and has an active delivery, drawActiveDriverRoute() already handles it.
        // But for keyword filter, we need to ensure the most relevant active route is highlighted if applicable.
        // This logic is mostly handled by the `selectedId` branch.
    }

    // --- Event Listeners ---
    document.addEventListener('DOMContentLoaded', () => {
        initAllMapElements(); // Initialize map with all drivers and static routes on load
        
        // POLLING DRIVER POSISI DARI API BACKEND
setInterval(() => {
    fetch('https://trackingvektor.my.id/api/admin/driver-lokasi')
        .then(res => res.json())
        .then(data => {
            data.forEach(driver => {
                const index = drivers.findIndex(d => d.id === driver.id);
                if (index !== -1) {
                    drivers[index].latitude = parseFloat(driver.latitude);
                    drivers[index].longitude = parseFloat(driver.longitude);

                    const selectedDriverId = document.getElementById('filterDriver').value;
                    const keyword = document.getElementById('searchDriver').value.toLowerCase();

                    const driverIsVisible = 
                        (!selectedDriverId && (!keyword || driver.nama.toLowerCase().includes(keyword))) ||
                        (selectedDriverId == driver.id);

                    if (driverIsVisible) {
                        addOrUpdateDriverMarker(drivers[index]); // ✅ pakai yang sudah diperbarui
                    }
                }
            });
        });
}, 5000);



        // Filter and search input change listeners
        document.getElementById('filterDriver').addEventListener('change', applyFilters);
        document.getElementById('searchDriver').addEventListener('input', applyFilters);

        // --- Pusher Real-time Updates ---
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: 'local', // Assuming 'local' is configured in your broadcasting.php
            wsHost: window.location.hostname,
            wsPort: 6001, // Or your configured Websockets port
            forceTLS: false,
            disableStats: true,
        });

        // Listen for driver location updates
        Echo.channel('driver-location')
            .listen('.driver.updated', (e) => {
                let updatedDriver = null;
                const index = drivers.findIndex(d => d.id == e.driverId);
                if (index !== -1) {
                    // Update existing driver's coordinates in the global drivers array
                    drivers[index].latitude = e.latitude;
                    drivers[index].longitude = e.longitude;
                    updatedDriver = drivers[index];
                } else {
                    // If a new driver appears (unlikely in this context, but for robustness)
                    updatedDriver = {
                        id: e.driverId,
                        nama: `Driver ${e.driverId}`,
                        latitude: e.latitude,
                        longitude: e.longitude,
                        kendaraan: { nama: 'Tidak diketahui' } // Placeholder if no vehicle data
                    };
                    drivers.push(updatedDriver); // Add new driver to the global list
                }

                updatedDriver.latitude = parseFloat(updatedDriver.latitude);
                updatedDriver.longitude = parseFloat(updatedDriver.longitude);

                // Update the driver's marker on the map AND re-evaluate filtering
                if (updatedDriver) {
                    const selectedDriverId = document.getElementById('filterDriver').value;
                    const keyword = document.getElementById('searchDriver').value.toLowerCase();

                    // Only update marker/route if the driver is currently visible based on filters
                    const driverIsVisible = 
                        (!selectedDriverId && (!keyword || updatedDriver.nama.toLowerCase().includes(keyword))) || 
                        (selectedDriverId == updatedDriver.id);

                    if (driverIsVisible) {
                        addOrUpdateDriverMarker(updatedDriver); // Update the marker

                        const relatedPengiriman = pengirimen.find(p => p.driver_id == updatedDriver.id);
                        if (relatedPengiriman && (selectedDriverId == updatedDriver.id)) { // Only show active route if this driver is selected
                            drawActiveDriverRoute(updatedDriver, relatedPengiriman);
                        } else if (activeDriverRouteControl && selectedDriverId != updatedDriver.id) {
                            // If this driver was previously selected, but now another is, clear its active route
                            map.removeControl(activeDriverRouteControl);
                            activeDriverRouteControl = null;
                            document.getElementById('route-info').classList.add('d-none');
                        }
                    } else {
                        // If driver is not visible, ensure their marker is removed if it somehow got added
                        if (driverMarkers[updatedDriver.id]) {
                            map.removeLayer(driverMarkers[updatedDriver.id]);
                            delete driverMarkers[updatedDriver.id];
                        }
                    }
                }
            });
    });
</script>
@endpush