@extends('admin.layouts.app')

@section('content')

<!-- HEADER -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">
        Dashboard 
        <span class="text-[#1DA1F2]">TopTop</span>
        <span class="text-[#FFC107]">Go</span>
    </h1>
    <p class="text-gray-500 text-sm mt-2">
        Vue globale de la plateforme
    </p>
</div>

<!-- =============================== -->
<!-- FILTRES VEHICULES -->
<!-- =============================== -->

<div class="bg-white p-6 rounded-2xl shadow-md mb-8">
    <h2 class="text-lg font-bold mb-4 text-gray-700">
        🔎 Filtrer un véhicule
    </h2>

    <form method="GET" action="{{ route('admin.dashboard') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">

            <input type="text" name="chauffeur"
                   value="{{ request('chauffeur') }}"
                   placeholder="Nom du chauffeur"
                   class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-[#1DA1F2] outline-none">

            <input type="text" name="matricule"
                   value="{{ request('matricule') }}"
                   placeholder="Numéro matricule"
                   class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-[#1DA1F2] outline-none">

            <input type="text" name="couleur"
                   value="{{ request('couleur') }}"
                   placeholder="Couleur véhicule"
                   class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-[#1DA1F2] outline-none">

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-[#1DA1F2] text-white rounded-xl font-semibold
                               hover:bg-[#FFC107] hover:text-black transition duration-300 py-2">
                    Rechercher
                </button>

                <a href="{{ route('admin.dashboard') }}"
                   class="flex-1 bg-gray-200 text-gray-700 rounded-xl text-center py-2
                          hover:bg-gray-300 transition">
                    Reset
                </a>
            </div>

        </div>
    </form>
</div>

<!-- =============================== -->
<!-- CARTE -->
<!-- =============================== -->

<div class="bg-white p-6 rounded-2xl shadow-md mb-10">
    <h2 class="text-lg font-bold mb-4 text-gray-700">
        📍 Suivi des chauffeurs en Afrique Centrale
    </h2>
    <div id="map" class="w-full h-96 rounded-xl"></div>
</div>

<!-- =============================== -->
<!-- CARTES STATISTIQUES DYNAMIQUES -->
<!-- =============================== -->

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    <!-- UTILISATEURS -->
    <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-blue-500
                transform transition duration-300 hover:-translate-y-2 hover:scale-105 hover:shadow-xl cursor-pointer">
        <p class="text-gray-500 text-sm">Utilisateurs</p>
        <h2 class="text-3xl font-bold text-blue-500 mt-2">
            {{ number_format($stats['total_users'] ?? 0) }}
        </h2>
        <p class="text-green-500 text-sm mt-2">
            +{{ number_format($stats['new_users_today'] ?? 0) }} aujourd’hui
        </p>
    </div>

    <!-- CHAUFFEURS -->
    <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-yellow-500
                transform transition duration-300 hover:-translate-y-2 hover:scale-105 hover:shadow-xl cursor-pointer">
        <p class="text-gray-500 text-sm">Chauffeurs actifs</p>
        <h2 class="text-3xl font-bold text-yellow-500 mt-2">
            {{ number_format($stats['active_drivers'] ?? 0) }}
        </h2>
        <p class="text-gray-500 text-sm mt-2">
            {{ number_format($stats['online_drivers'] ?? 0) }} en ligne
        </p>
    </div>

    <!-- COURSES -->
    <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-black
                transform transition duration-300 hover:-translate-y-2 hover:scale-105 hover:shadow-xl cursor-pointer">
        <p class="text-gray-500 text-sm">Courses aujourd'hui</p>
        <h2 class="text-3xl font-bold text-black mt-2">
            {{ number_format($stats['today_rides'] ?? 0) }}
        </h2>
        <p class="text-gray-500 text-sm mt-2">
            {{ number_format($stats['active_rides'] ?? 0) }} en cours
        </p>
    </div>

    <!-- REVENUS -->
    <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-green-500
                transform transition duration-300 hover:-translate-y-2 hover:scale-105 hover:shadow-xl cursor-pointer">
        <p class="text-gray-500 text-sm">Revenus du jour</p>
        <h2 class="text-3xl font-bold text-green-500 mt-2">
            {{ number_format($stats['today_revenue'] ?? 0) }} XAF
        </h2>
        <p class="text-gray-500 text-sm mt-2">
            Commission : {{ number_format($stats['today_commission'] ?? 0) }} XAF
        </p>
    </div>

</div>

@endsection


@push('scripts')

<style>
.plaque-label {
    background: #1DA1F2;
    color: white;
    border-radius: 6px;
    padding: 2px 6px;
    font-size: 12px;
    font-weight: bold;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    var map = L.map('map').setView([3.5, 15], 6);

    var bounds = L.latLngBounds([-5, 8],[15, 25]);
    map.setMaxBounds(bounds);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var carIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/744/744465.png',
        iconSize: [35, 35],
    });

    var drivers = @json($drivers ?? []);
    var markers = [];

    drivers.forEach(function(driver){

        if (!driver.current_latitude || !driver.current_longitude) return;

        var marker = L.marker(
            [driver.current_latitude, driver.current_longitude],
            {icon: carIcon}
        ).addTo(map)
        .bindPopup(`
            <strong>🚗 Plaque :</strong> ${driver.vehicle_plate_number ?? 'N/A'}<br>
            <strong>👤 Chauffeur :</strong> ${driver.user?.name ?? 'N/A'}<br>
            <strong>🎨 Couleur :</strong> ${driver.vehicle_color ?? 'N/A'}<br>
            <strong>⭐ Note :</strong> ${driver.rating_average ?? '0'}
        `)
        .bindTooltip(driver.vehicle_plate_number ?? 'N/A', {
            permanent: true,
            direction: "top",
            offset: [0, -15],
            className: "plaque-label"
        });

        markers.push(marker);
    });

    if (markers.length === 1) {
        map.setView(markers[0].getLatLng(), 14);
        markers[0].openPopup();
    }

    if (markers.length > 1) {
        var group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.2));
    }

});
</script>

@endpush