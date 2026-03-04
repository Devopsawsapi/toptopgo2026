@extends('admin.layouts.app')

@section('title', 'Géolocalisation Live')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="p-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🗺️ Géolocalisation Live</h1>
            <p class="text-sm text-gray-500 mt-1">Suivi des trajets en temps réel</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse inline-block"></span>
            Mise à jour auto toutes les 15s
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" id="statsBar">
        <div class="bg-white rounded-xl shadow-sm border p-4 border-l-4 border-l-blue-500">
            <div class="text-xs text-gray-500">Total trajets</div>
            <div class="text-2xl font-bold text-blue-600" id="statTotal">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 border-l-4 border-l-yellow-500">
            <div class="text-xs text-gray-500">En attente</div>
            <div class="text-2xl font-bold text-yellow-600" id="statPending">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 border-l-4 border-l-green-500">
            <div class="text-xs text-gray-500">En cours</div>
            <div class="text-2xl font-bold text-green-600" id="statProgress">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 border-l-4 border-l-gray-400">
            <div class="text-xs text-gray-500">Terminés</div>
            <div class="text-2xl font-bold text-gray-600" id="statDone">—</div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex gap-3 flex-wrap items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                <select id="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="pending">⏳ En attente</option>
                    <option value="accepted">✅ Accepté</option>
                    <option value="in_progress">🚗 En cours</option>
                    <option value="completed">🏁 Terminé</option>
                    <option value="cancelled">❌ Annulé</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Du</label>
                <input type="date" id="fromDate" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Au</label>
                <input type="date" id="toDate" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button id="filterBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                🔍 Filtrer
            </button>
            <button id="resetBtn" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                ✕ Reset
            </button>
        </div>
    </div>

    {{-- TABLEAU --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h3 class="font-semibold text-gray-700">📋 Liste des trajets</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Chauffeur</th>
                        <th class="px-4 py-3 text-left">Client</th>
                        <th class="px-4 py-3 text-left">Départ → Arrivée</th>
                        <th class="px-4 py-3 text-left">Date / Heure</th>
                        <th class="px-4 py-3 text-left">Places</th>
                        <th class="px-4 py-3 text-left">Prix/siège</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                    </tr>
                </thead>
                <tbody id="tripsTableBody">
                    <tr><td colspan="7" class="text-center p-6 text-gray-400">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- CARTE --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="p-4 border-b">
            <h3 class="font-semibold text-gray-700">🗺️ Carte des trajets</h3>
        </div>
        <div id="map" style="height: 500px;"></div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const tripsUrl = "{{ route('admin.geolocation.trips') }}";

let map = L.map('map').setView([0, 20], 3);
let markers = [];

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(map);

const statusLabels = {
    pending:     '⏳ En attente',
    accepted:    '✅ Accepté',
    in_progress: '🚗 En cours',
    completed:   '🏁 Terminé',
    cancelled:   '❌ Annulé',
};

const statusColors = {
    pending:     'bg-yellow-100 text-yellow-700',
    accepted:    'bg-blue-100 text-blue-700',
    in_progress: 'bg-green-100 text-green-700',
    completed:   'bg-gray-100 text-gray-600',
    cancelled:   'bg-red-100 text-red-600',
};

function clearMarkers() {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
}

function loadTrips() {
    const status = document.getElementById('statusFilter').value;
    const from   = document.getElementById('fromDate').value;
    const to     = document.getElementById('toDate').value;
    const url    = `${tripsUrl}?status=${status}&from=${from}&to=${to}`;

    fetch(url)
        .then(r => r.json())
        .then(data => {
            const trips = data.data || [];
            const tbody = document.getElementById('tripsTableBody');
            clearMarkers();

            // Stats
            document.getElementById('statTotal').textContent   = trips.length;
            document.getElementById('statPending').textContent  = trips.filter(t => t.status === 'pending').length;
            document.getElementById('statProgress').textContent = trips.filter(t => t.status === 'in_progress').length;
            document.getElementById('statDone').textContent     = trips.filter(t => t.status === 'completed').length;

            if (trips.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center p-6 text-gray-400">Aucun trajet trouvé</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            trips.forEach(trip => {
                const colorClass = statusColors[trip.status] || 'bg-gray-100 text-gray-600';
                const label      = statusLabels[trip.status] || trip.status;
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 border-b border-gray-50 transition cursor-pointer';
                row.innerHTML = `
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">${trip.driver?.name || '—'}</div>
                        <div class="text-xs text-gray-400">${trip.driver?.vehicle_plate || ''} ${trip.driver?.vehicle_type || ''}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">${trip.user?.name || '<span class="text-gray-400 italic">Sans client</span>'}</td>
                    <td class="px-4 py-3">
                        <div class="text-gray-800">${trip.pickup_address}</div>
                        <div class="text-xs text-gray-400">→ ${trip.dropoff_address}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        ${trip.departure_date || '—'}<br>${trip.departure_time || ''}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">${trip.available_seats || '—'}</td>
                    <td class="px-4 py-3 text-gray-600">${trip.price_per_seat ? trip.price_per_seat + ' FCFA' : '—'}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded-full font-medium ${colorClass}">${label}</span>
                    </td>
                `;

                // Zoom sur la carte au clic
                row.addEventListener('click', () => {
                    if (trip.pickup_lat && trip.pickup_lng) {
                        document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
                        setTimeout(() => map.setView([trip.pickup_lat, trip.pickup_lng], 13), 400);
                    }
                });

                tbody.appendChild(row);

                // Marqueurs carte
                if (trip.pickup_lat && trip.pickup_lng) {
                    const m = L.marker([trip.pickup_lat, trip.pickup_lng])
                        .bindPopup(`<b>🚀 Départ</b><br>${trip.pickup_address}<br>
                            Chauffeur: ${trip.driver?.name || '—'}<br>
                            ${trip.departure_date || ''} ${trip.departure_time || ''}`)
                        .addTo(map);
                    markers.push(m);
                }

                if (trip.dropoff_lat && trip.dropoff_lng) {
                    const m = L.marker([trip.dropoff_lat, trip.dropoff_lng], {
                        icon: L.icon({ iconUrl: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png', iconSize: [32, 32] })
                    }).bindPopup(`<b>🏁 Arrivée</b><br>${trip.dropoff_address}`)
                      .addTo(map);
                    markers.push(m);
                }

                if (trip.pickup_lat && trip.pickup_lng && trip.dropoff_lat && trip.dropoff_lng) {
                    const line = L.polyline([
                        [trip.pickup_lat, trip.pickup_lng],
                        [trip.dropoff_lat, trip.dropoff_lng]
                    ], { color: '#3b82f6', weight: 2, dashArray: '6,4' }).addTo(map);
                    markers.push(line);
                }
            });

            if (markers.length > 0) {
                const group = new L.featureGroup(markers.filter(m => m.getLatLng));
                if (group.getBounds().isValid()) {
                    map.fitBounds(group.getBounds().pad(0.2));
                }
            }
        })
        .catch(e => console.error('Erreur chargement trajets:', e));
}

document.getElementById('filterBtn').addEventListener('click', loadTrips);
document.getElementById('resetBtn').addEventListener('click', () => {
    document.getElementById('statusFilter').value = '';
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
    loadTrips();
});

loadTrips();
setInterval(loadTrips, 15000);
</script>
@endpush