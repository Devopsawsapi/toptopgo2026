@extends('admin.layouts.app')

@section('content')
<div class="p-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🆘 Gestion des Alertes SOS</h1>
            <p class="text-sm text-gray-500 mt-1">Surveillance en temps réel des alertes d'urgence</p>
        </div>
        @if($totalActive > 0)
        <form method="POST" action="{{ route('admin.sos.treat-all') }}"
              onsubmit="return confirm('Marquer toutes les alertes actives comme traitées ?')">
            @csrf
            <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                ✓ Tout marquer traité ({{ $totalActive }})
            </button>
        </form>
        @endif
    </div>

    {{-- ===== STATS ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-red-500">
            <div class="text-sm text-gray-500 mb-1">Alertes actives</div>
            <div class="text-3xl font-bold text-red-500 flex items-center gap-2">
                {{ $totalActive }}
                @if($totalActive > 0)
                    <span class="w-3 h-3 rounded-full bg-red-500 animate-ping inline-block"></span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-green-500">
            <div class="text-sm text-gray-500 mb-1">Traitées</div>
            <div class="text-3xl font-bold text-green-600">{{ $totalTreated }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-blue-500">
            <div class="text-sm text-gray-500 mb-1">Aujourd'hui</div>
            <div class="text-3xl font-bold text-blue-600">{{ $totalToday }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Total</div>
            <div class="text-3xl font-bold text-gray-800">{{ $totalAll }}</div>
        </div>
    </div>

    {{-- ===== CARTE SOS ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-lg">🗺️</span>
                <div>
                    <h2 class="font-bold text-gray-800">Carte des alertes actives</h2>
                    <p class="text-xs text-gray-400">Mis à jour toutes les 10 secondes</p>
                </div>
                @if($totalActive > 0)
                    <span class="bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full font-medium animate-pulse">
                        🆘 {{ $totalActive }} alerte(s) active(s)
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Chauffeur</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span> Utilisateur</span>
            </div>
        </div>
        <div id="sosMap" style="height: 350px; z-index: 1;"></div>
    </div>

    {{-- ===== FILTRES ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.sos.index') }}" class="flex gap-3 flex-wrap items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="all"     {{ request('status') === 'all'     ? 'selected' : '' }}>Tous</option>
                    <option value="active"  {{ request('status') === 'active'  ? 'selected' : '' }}>🔴 Actives</option>
                    <option value="treated" {{ request('status') === 'treated' ? 'selected' : '' }}>✅ Traitées</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="sender_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Tous</option>
                    <option value="driver" {{ request('sender_type') === 'driver' ? 'selected' : '' }}>🚗 Chauffeurs</option>
                    <option value="user"   {{ request('sender_type') === 'user'   ? 'selected' : '' }}>👤 Utilisateurs</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                🔍 Filtrer
            </button>
            <a href="{{ route('admin.sos.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                ✕ Reset
            </a>
        </form>
    </div>

    {{-- ===== LISTE ALERTES ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">
                📋 Liste des alertes
                <span class="ml-2 bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full">
                    {{ $alerts->total() }}
                </span>
            </h3>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($alerts as $alert)
                @php
                    $isDriver = str_contains($alert->sender_type, 'Driver');
                    $senderName = ($alert->sender->first_name ?? '—') . ' ' . ($alert->sender->last_name ?? '');
                @endphp

                <div class="p-4 hover:bg-gray-50 transition {{ $alert->status === 'active' ? 'border-l-4 border-l-red-500' : '' }}">
                    <div class="flex items-start justify-between gap-4">

                        {{-- Infos alerte --}}
                        <div class="flex items-start gap-4 flex-1">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0
                                {{ $isDriver ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $isDriver ? '🚗' : '👤' }}
                            </div>

                            <div class="flex-1">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="font-semibold text-gray-800">{{ $senderName }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        {{ $isDriver ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ $isDriver ? 'Chauffeur' : 'Utilisateur' }}
                                    </span>
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        {{ $alert->status === 'active' ? 'bg-red-500 text-white animate-pulse' : 'bg-green-100 text-green-700' }}">
                                        {{ $alert->status === 'active' ? '🆘 ACTIVE' : '✅ Traitée' }}
                                    </span>
                                </div>

                                @if($alert->message)
                                    <p class="text-sm text-gray-600 mt-1">{{ $alert->message }}</p>
                                @endif

                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400 flex-wrap">
                                    <span>🕐 {{ $alert->created_at->format('d/m/Y H:i') }}
                                        ({{ $alert->created_at->diffForHumans() }})</span>
                                    @if($alert->trip_id)
                                        <span>🚕 Course #{{ $alert->trip_id }}</span>
                                    @endif
                                    @if($alert->lat && $alert->lng)
                                        <span>📍 {{ number_format($alert->lat, 4) }}, {{ number_format($alert->lng, 4) }}</span>
                                    @endif
                                    @if($alert->status === 'treated' && $alert->treatedBy)
                                        <span>✅ Traité par {{ $alert->treatedBy->name ?? '—' }}
                                            le {{ $alert->treated_at?->format('d/m/Y H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($alert->lat && $alert->lng)
                                <button onclick="zoomSos({{ $alert->lat }}, {{ $alert->lng }})"
                                    class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs transition">
                                    📍 Localiser
                                </button>
                            @endif

                            <a href="{{ route('admin.sos.show', $alert->id) }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs transition">
                                👁 Détail
                            </a>

                            @if($alert->status === 'active')
                                <form method="POST" action="{{ route('admin.sos.treat', $alert->id) }}">
                                    @csrf
                                    <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs transition">
                                        ✓ Traiter
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.sos.destroy', $alert->id) }}"
                                  onsubmit="return confirm('Supprimer cette alerte ?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-xs transition">
                                    🗑
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-400">
                    <div class="text-5xl mb-3">✅</div>
                    <p class="font-medium text-gray-500">Aucune alerte SOS</p>
                    <p class="text-sm mt-1">Tout est calme pour le moment</p>
                </div>
            @endforelse
        </div>

        @if($alerts->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $alerts->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Carte SOS ─────────────────────────────────────────────────
const sosMap = L.map('sosMap').setView([2.0, 15.0], 4);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(sosMap);

let sosMarkers = {};

function makeSosIcon(type) {
    const color = type === 'driver' ? '#ef4444' : '#f97316';
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="50" viewBox="0 0 40 50">
            <ellipse cx="20" cy="48" rx="9" ry="3" fill="rgba(0,0,0,0.2)"/>
            <path d="M20 0 C9 0 0 9 0 20 C0 33 20 50 20 50 C20 50 40 33 40 20 C40 9 31 0 20 0Z"
                  fill="${color}" stroke="white" stroke-width="2"/>
            <text x="20" y="26" text-anchor="middle" font-size="18" fill="white">🆘</text>
        </svg>`;
    return L.divIcon({ html: svg, iconSize: [40,50], iconAnchor: [20,50], popupAnchor: [0,-50], className: '' });
}

function updateSosMap(alerts) {
    const seen = new Set();
    alerts.forEach(a => {
        if (!a.lat || !a.lng) return;
        seen.add(a.id);

        const popup = `
            <div style="min-width:180px; font-family:sans-serif;">
                <div style="font-weight:bold; color:#ef4444; font-size:14px; margin-bottom:6px;">
                    🆘 Alerte SOS
                </div>
                <div style="font-size:12px; color:#555; line-height:1.8;">
                    ${a.sender_type === 'driver' ? '🚗' : '👤'} <b>${a.sender_name}</b><br>
                    ${a.message ? '💬 ' + a.message + '<br>' : ''}
                    🕐 ${a.created_at}<br>
                    ${a.trip_id ? '🚕 Course #' + a.trip_id : ''}
                </div>
                <a href="/admin/sos/${a.id}"
                   style="display:inline-block; margin-top:8px; background:#ef4444; color:white;
                          padding:4px 10px; border-radius:6px; font-size:11px; text-decoration:none;">
                    Voir le détail →
                </a>
            </div>`;

        if (sosMarkers[a.id]) {
            sosMarkers[a.id].setPopupContent(popup);
        } else {
            sosMarkers[a.id] = L.marker([a.lat, a.lng], { icon: makeSosIcon(a.sender_type) })
                .addTo(sosMap)
                .bindPopup(popup)
                .openPopup();
        }
    });

    // Supprimer marqueurs traités
    Object.keys(sosMarkers).forEach(id => {
        if (!seen.has(parseInt(id))) {
            sosMap.removeLayer(sosMarkers[id]);
            delete sosMarkers[id];
        }
    });

    // Ajuster vue si alertes
    if (alerts.length > 0) {
        const coords = alerts.filter(a => a.lat && a.lng).map(a => [a.lat, a.lng]);
        if (coords.length === 1) {
            sosMap.setView(coords[0], 14);
        } else if (coords.length > 1) {
            sosMap.fitBounds(L.latLngBounds(coords), { padding: [40, 40] });
        }
    }
}

function zoomSos(lat, lng) {
    sosMap.scrollIntoView ? sosMap.getContainer().scrollIntoView({ behavior: 'smooth' }) : null;
    document.getElementById('sosMap').scrollIntoView({ behavior: 'smooth' });
    setTimeout(() => sosMap.setView([lat, lng], 16), 400);
}

// Fetch alertes actives
function fetchSosAlerts() {
    fetch("{{ route('admin.sos.live') }}", {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => updateSosMap(data.alerts))
    .catch(e => console.error('SOS fetch error:', e));
}

fetchSosAlerts();
setInterval(fetchSosAlerts, 10000);

// Son d'alerte si nouvelles alertes actives
@if($totalActive > 0)
    document.title = '🆘 {{ $totalActive }} SOS - TopTopGo Admin';
@endif
</script>
@endpush