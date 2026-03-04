@extends('admin.layouts.app')

@section('title', 'Géolocalisation Live')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Géolocalisation Live</h1>

    {{-- Filtres --}}
    <div class="flex gap-4 mb-4">
        <select id="statusFilter" class="border rounded p-2">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="started">En cours</option>
            <option value="completed">Terminé</option>
            <option value="cancelled">Annulé</option>
        </select>

        <input type="date" id="fromDate" class="border rounded p-2" placeholder="De">
        <input type="date" id="toDate" class="border rounded p-2" placeholder="À">
        <button id="filterBtn" class="bg-blue-600 text-white px-4 py-2 rounded">Filtrer</button>
    </div>

    {{-- Tableau --}}
    <div class="overflow-x-auto mb-6">
        <table class="w-full table-auto border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">Chauffeur</th>
                    <th class="px-4 py-2 border">Client</th>
                    <th class="px-4 py-2 border">Départ → Arrivée</th>
                    <th class="px-4 py-2 border">Distance</th>
                    <th class="px-4 py-2 border">Montant</th>
                    <th class="px-4 py-2 border">Statut</th>
                </tr>
            </thead>
            <tbody id="tripsTableBody">
                <tr><td colspan="6" class="text-center p-4">Chargement...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Carte --}}
    <div id="map" style="height: 500px;" class="border rounded"></div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
const tripsUrl = "{{ route('api.admin.trips') }}";
let map = L.map('map').setView([0, 0], 2);
let markers = [];

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

function clearMarkers() {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
}

function loadTrips() {
    const status = document.getElementById('statusFilter').value;
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;

    let url = tripsUrl + `?status=${status}&from=${from}&to=${to}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('tripsTableBody');
            tbody.innerHTML = '';
            clearMarkers();

            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4">Aucun trajet trouvé</td></tr>';
                return;
            }

            data.data.forEach(trip => {
                // Tableau
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="border px-4 py-2">${trip.driver?.name || '-'}</td>
                    <td class="border px-4 py-2">${trip.client?.name || '-'}</td>
                    <td class="border px-4 py-2">${trip.pickup_address} → ${trip.dropoff_address}</td>
                    <td class="border px-4 py-2">${trip.distance_km || '-'} km</td>
                    <td class="border px-4 py-2">${trip.amount || '-'} FCFA</td>
                    <td class="border px-4 py-2 capitalize">${trip.status}</td>
                `;
                tbody.appendChild(row);

                // Carte
                if (trip.pickup_lat && trip.pickup_lng && trip.dropoff_lat && trip.dropoff_lng) {
                    const pickupMarker = L.marker([trip.pickup_lat, trip.pickup_lng])
                        .bindPopup(`<strong>Départ</strong><br>${trip.pickup_address}<br>Chauffeur: ${trip.driver?.name || '-'}`);
                    pickupMarker.addTo(map);
                    markers.push(pickupMarker);

                    const dropoffMarker = L.marker([trip.dropoff_lat, trip.dropoff_lng], {
                        icon: L.icon({iconUrl:'https://maps.google.com/mapfiles/ms/icons/green-dot.png',iconSize:[32,32]})
                    }).bindPopup(`<strong>Arrivée</strong><br>${trip.dropoff_address}<br>Client: ${trip.client?.name || '-'}`);
                    dropoffMarker.addTo(map);
                    markers.push(dropoffMarker);

                    const line = L.polyline([
                        [trip.pickup_lat, trip.pickup_lng],
                        [trip.dropoff_lat, trip.dropoff_lng]
                    ], {color: 'blue'}).addTo(map);
                    markers.push(line);
                }
            });

            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            }
        });
}

document.getElementById('filterBtn').addEventListener('click', loadTrips);
loadTrips();
setInterval(loadTrips, 15000); // auto refresh toutes les 15s
</script>
@endsection