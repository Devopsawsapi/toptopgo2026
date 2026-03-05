@extends('layouts.admin')

@section('content')

<div class="space-y-6">

    {{-- ══════════════════════════════════════════════════════════
         EN-TÊTE
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 Trajets & Courses</h1>
            <p class="text-sm text-gray-500 mt-1">
                Tous les trajets créés par les chauffeurs, leurs statuts et réservations associées.
            </p>
        </div>

        {{-- Compteurs rapides --}}
        <div class="flex flex-wrap gap-3">
            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-2 rounded-lg">
                🚗 Total : {{ $stats['total'] ?? 0 }}
            </span>
            <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-2 rounded-lg">
                ⏳ En attente : {{ $stats['pending'] ?? 0 }}
            </span>
            <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-2 rounded-lg">
                🚗 En cours : {{ $stats['in_progress'] ?? 0 }}
            </span>
            <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-2 rounded-lg">
                🏁 Terminés : {{ $stats['completed'] ?? 0 }}
            </span>
            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-2 rounded-lg">
                ❌ Annulés : {{ $stats['cancelled'] ?? 0 }}
            </span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         FILTRES
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="{{ route('admin.trips.index') }}"
              class="flex flex-wrap gap-3 items-end">

            {{-- Recherche --}}
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1 font-semibold uppercase">
                    Recherche
                </label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Chauffeur, départ, destination..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Statut --}}
            <div class="min-w-[160px]">
                <label class="block text-xs text-gray-500 mb-1 font-semibold uppercase">
                    Statut
                </label>
                <select name="status"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Tous les statuts</option>
                    <option value="pending"     {{ request('status') == 'pending'     ? 'selected' : '' }}>⏳ En attente</option>
                    <option value="accepted"    {{ request('status') == 'accepted'    ? 'selected' : '' }}>✅ Accepté</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>🚗 En cours</option>
                    <option value="completed"   {{ request('status') == 'completed'   ? 'selected' : '' }}>🏁 Terminé</option>
                    <option value="cancelled"   {{ request('status') == 'cancelled'   ? 'selected' : '' }}>❌ Annulé</option>
                </select>
            </div>

            {{-- Date début --}}
            <div class="min-w-[150px]">
                <label class="block text-xs text-gray-500 mb-1 font-semibold uppercase">
                    Date début
                </label>
                <input type="date" name="from" value="{{ request('from') }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Date fin --}}
            <div class="min-w-[150px]">
                <label class="block text-xs text-gray-500 mb-1 font-semibold uppercase">
                    Date fin
                </label>
                <input type="date" name="to" value="{{ request('to') }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Boutons --}}
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-[#1DA1F2] text-white px-4 py-2 rounded-lg text-sm font-semibold
                           hover:bg-blue-600 transition">
                    🔍 Filtrer
                </button>
                <a href="{{ route('admin.trips.index') }}"
                    class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-semibold
                           hover:bg-gray-200 transition">
                    ✕ Reset
                </a>
            </div>

        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TABLEAU
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- En-tête tableau --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-700 text-sm uppercase tracking-wide">
                📋 Liste des trajets
                <span class="ml-2 bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">
                    {{ $trips->total() }} résultats
                </span>
            </h2>
            <a href="{{ route('admin.trips.index') }}?export=csv"
               class="text-xs bg-green-100 text-green-700 font-semibold px-3 py-1.5 rounded-lg
                      hover:bg-green-200 transition">
                📥 Exporter CSV
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Chauffeur</th>
                        <th class="px-4 py-3 text-left">Itinéraire</th>
                        <th class="px-4 py-3 text-left">Date & Heure</th>
                        <th class="px-4 py-3 text-left">Véhicule</th>
                        <th class="px-4 py-3 text-center">Places</th>
                        <th class="px-4 py-3 text-center">Bagages</th>
                        <th class="px-4 py-3 text-left">Prix/place</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-left">Réservations</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    @forelse($trips as $trip)

                    @php
                        $driver   = $trip->driver;
                        $vehicle  = $trip->vehicle ?? $driver?->vehicle ?? null;
                        $bookings = $trip->bookings ?? collect();

                        $statusConfig = [
                            'pending'     => ['label' => '⏳ En attente',  'class' => 'bg-yellow-100 text-yellow-700'],
                            'accepted'    => ['label' => '✅ Accepté',     'class' => 'bg-blue-100 text-blue-700'],
                            'in_progress' => ['label' => '🚗 En cours',    'class' => 'bg-indigo-100 text-indigo-700'],
                            'completed'   => ['label' => '🏁 Terminé',     'class' => 'bg-green-100 text-green-700'],
                            'cancelled'   => ['label' => '❌ Annulé',      'class' => 'bg-red-100 text-red-700'],
                        ];
                        $sc = $statusConfig[$trip->status] ?? ['label' => $trip->status, 'class' => 'bg-gray-100 text-gray-600'];

                        $confirmed = $bookings->whereIn('status', ['confirmed', 'accepted'])->count();
                        $pending   = $bookings->where('status', 'pending')->count();
                        $rejected  = $bookings->whereIn('status', ['rejected', 'cancelled'])->count();

                        $driverName  = $driver?->name ?? trim(($driver?->first_name ?? '') . ' ' . ($driver?->last_name ?? '')) ?: 'N/A';
                        $driverPhone = $driver?->phone ?? $driver?->telephone ?? '—';
                        $brand       = $vehicle?->brand ?? $vehicle?->make ?? $trip->vehicle_type ?? '—';
                        $plate       = $vehicle?->plate ?? $vehicle?->license_plate ?? '—';
                    @endphp

                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- ID --}}
                        <td class="px-4 py-3 text-gray-400 font-mono text-xs">
                            #{{ $trip->id }}
                        </td>

                        {{-- Chauffeur --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center
                                            text-orange-600 font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($driverName, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-xs leading-tight">
                                        {{ $driverName }}
                                    </p>
                                    <p class="text-gray-400 text-xs">{{ $driverPhone }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Itinéraire --}}
                        <td class="px-4 py-3 max-w-[180px]">
                            <div class="flex items-start gap-1.5">
                                <div class="flex flex-col items-center mt-1 flex-shrink-0">
                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <div class="w-0.5 h-4 bg-gray-200"></div>
                                    <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 truncate max-w-[150px]">
                                        {{ $trip->departure }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate max-w-[150px] mt-1">
                                        {{ $trip->destination }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Date & Heure --}}
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                            <p class="font-semibold">
                                {{ $trip->departure_date
                                    ? \Carbon\Carbon::parse($trip->departure_date)->format('d/m/Y')
                                    : '—' }}
                            </p>
                            <p class="text-gray-400">
                                {{ $trip->departure_time
                                    ? \Carbon\Carbon::parse($trip->departure_time)->format('H:i')
                                    : '—' }}
                            </p>
                        </td>

                        {{-- Véhicule --}}
                        <td class="px-4 py-3 text-xs">
                            <p class="font-semibold text-gray-700">
                                {{ $brand }}
                                {{ $vehicle?->model ?? '' }}
                            </p>
                            <p class="text-gray-400 font-mono">{{ $plate }}</p>
                        </td>

                        {{-- Places --}}
                        <td class="px-4 py-3 text-center">
                            <span class="bg-blue-50 text-blue-600 font-bold text-xs px-2 py-1 rounded-lg">
                                {{ $trip->available_seats ?? '—' }}
                            </span>
                        </td>

                        {{-- Bagages --}}
                        <td class="px-4 py-3 text-xs text-center">
                            <p class="font-semibold text-gray-700">
                                {{ $trip->luggage_included ?? 0 }} inclus
                            </p>
                            @if(($trip->extra_luggage_fee ?? 0) > 0)
                                <p class="text-orange-500 font-semibold">
                                    +{{ number_format($trip->extra_luggage_fee, 0, '.', ' ') }} FCFA
                                </p>
                            @endif
                        </td>

                        {{-- Prix --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-orange-500 font-bold text-sm">
                                {{ number_format($trip->price_per_seat ?? 0, 0, '.', ' ') }} FCFA
                            </span>
                        </td>

                        {{-- Statut --}}
                        <td class="px-4 py-3">
                            <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $sc['class'] }}">
                                {{ $sc['label'] }}
                            </span>
                        </td>

                        {{-- Réservations résumé --}}
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-0.5 text-xs">
                                @if($confirmed > 0)
                                    <span class="text-green-600 font-semibold">✅ {{ $confirmed }} confirmée(s)</span>
                                @endif
                                @if($pending > 0)
                                    <span class="text-yellow-600 font-semibold">⏳ {{ $pending }} en attente</span>
                                @endif
                                @if($rejected > 0)
                                    <span class="text-red-400">❌ {{ $rejected }} rejetée(s)</span>
                                @endif
                                @if($bookings->isEmpty())
                                    <span class="text-gray-400 italic">Aucune</span>
                                @endif
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 text-center">
                            <button onclick="openTripModal({{ $trip->id }})"
                                class="bg-[#1DA1F2] text-white text-xs px-3 py-1.5 rounded-lg
                                       hover:bg-blue-600 transition font-semibold">
                                👁 Détail
                            </button>
                        </td>

                    </tr>

                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <span class="text-5xl">🚗</span>
                                <p class="text-gray-500 font-semibold">Aucun trajet trouvé</p>
                                <p class="text-gray-400 text-xs">
                                    Modifiez les filtres ou attendez que des chauffeurs publient des trajets
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $trips->withQueryString()->links() }}
        </div>

    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL DÉTAIL TRAJET
══════════════════════════════════════════════════════════ --}}
<div id="trip-modal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden"
     onclick="closeTripModal(event)">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto"
         onclick="event.stopPropagation()">

        {{-- Header modal --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h2 class="font-bold text-gray-800 text-lg">📋 Détail du trajet</h2>
            <button onclick="document.getElementById('trip-modal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none font-bold">
                &times;
            </button>
        </div>

        {{-- Contenu modal (injecté dynamiquement) --}}
        <div id="modal-content" class="p-6">
            <div class="flex items-center justify-center py-12">
                <div class="animate-spin w-8 h-8 border-4 border-[#1DA1F2] border-t-transparent rounded-full"></div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Ouvrir le modal ─────────────────────────────────────────────────────────
async function openTripModal(tripId) {
    document.getElementById('trip-modal').classList.remove('hidden');
    document.getElementById('modal-content').innerHTML = `
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin w-8 h-8 border-4 border-[#1DA1F2] border-t-transparent rounded-full"></div>
        </div>`;

    try {
        const res = await fetch(`/admin/trips/${tripId}/detail`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        renderModal(data);
    } catch (e) {
        document.getElementById('modal-content').innerHTML =
            `<p class="text-red-500 text-center py-8">❌ Erreur lors du chargement du trajet.</p>`;
    }
}

// ── Fermer le modal (clic hors) ─────────────────────────────────────────────
function closeTripModal(e) {
    if (e.target === document.getElementById('trip-modal')) {
        document.getElementById('trip-modal').classList.add('hidden');
    }
}

// ── Fermeture clavier ESC ───────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('trip-modal').classList.add('hidden');
    }
});

// ── Rendu HTML du modal ─────────────────────────────────────────────────────
function renderModal(d) {
    const statusLabels = {
        pending:     '⏳ En attente',
        accepted:    '✅ Accepté',
        in_progress: '🚗 En cours',
        completed:   '🏁 Terminé',
        cancelled:   '❌ Annulé',
    };
    const statusClasses = {
        pending:     'bg-yellow-100 text-yellow-700',
        accepted:    'bg-blue-100 text-blue-700',
        in_progress: 'bg-indigo-100 text-indigo-700',
        completed:   'bg-green-100 text-green-700',
        cancelled:   'bg-red-100 text-red-700',
    };

    const driver   = d.driver  || {};
    const vehicle  = d.vehicle || driver.vehicle || {};
    const bookings = d.bookings || [];
    const payment  = d.payment || null;

    const luggageFee = parseFloat(d.extra_luggage_fee || 0);
    const luggageInc = parseInt(d.luggage_included || 0);

    const confirmed    = bookings.filter(b => ['confirmed','accepted'].includes(b.status)).length;
    const pendingCount = bookings.filter(b => b.status === 'pending').length;
    const rejected     = bookings.filter(b => ['rejected','cancelled'].includes(b.status)).length;
    const totalRevenue = bookings
        .filter(b => ['confirmed','accepted','completed'].includes(b.status))
        .reduce((sum, b) => sum + parseFloat(b.amount || b.total_price || 0), 0);

    const driverName  = driver.name || `${driver.first_name||''} ${driver.last_name||''}`.trim() || '—';
    const driverPhone = driver.phone || driver.telephone || '—';
    const driverEmail = driver.email || '—';
    const brand = vehicle.brand || vehicle.make || d.vehicle_type || '—';
    const plate = vehicle.plate || vehicle.license_plate || '—';
    const model = vehicle.model || '';

    // Lignes réservations
    const bStatusHtml = {
        pending:   '<span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-0.5 rounded-lg">⏳ En attente</span>',
        confirmed: '<span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-lg">✅ Confirmée</span>',
        accepted:  '<span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-lg">✅ Acceptée</span>',
        rejected:  '<span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-lg">❌ Rejetée</span>',
        cancelled: '<span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-lg">🚫 Annulée</span>',
        completed: '<span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-lg">🏁 Terminée</span>',
    };

    const bookingRows = bookings.length === 0
        ? `<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic">Aucune réservation pour ce trajet</td></tr>`
        : bookings.map(b => {
            const client      = b.user || b.client || {};
            const clientName  = client.name || `${client.first_name||''} ${client.last_name||''}`.trim() || '—';
            const clientPhone = client.phone || client.telephone || '—';
            const seats       = b.seats || b.passengers || 1;
            const luggage     = b.luggage_count || 0;
            const amount      = parseFloat(b.amount || b.total_price || 0);
            const extraLug    = Math.max(0, luggage - (luggageInc * seats));
            const lugTotal    = extraLug * luggageFee;

            return `<tr class="hover:bg-gray-50 border-b border-gray-50">
                <td class="px-3 py-3 text-xs">
                    <p class="font-semibold text-gray-800">${clientName}</p>
                    <p class="text-gray-400">${clientPhone}</p>
                </td>
                <td class="px-3 py-3 text-center text-xs font-bold text-blue-600">${seats}</td>
                <td class="px-3 py-3 text-center text-xs">
                    <p class="font-semibold text-gray-700">${luggage} bagage(s)</p>
                    ${lugTotal > 0
                        ? `<p class="text-orange-500 font-bold">+${lugTotal.toLocaleString('fr-FR')} FCFA</p>`
                        : `<p class="text-gray-400">Inclus</p>`}
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="text-orange-500 font-bold text-sm">${amount.toLocaleString('fr-FR')} FCFA</span>
                </td>
                <td class="px-3 py-3 text-center">${bStatusHtml[b.status] || `<span class="text-gray-400 text-xs">${b.status}</span>`}</td>
            </tr>`;
        }).join('');

    // Infos paiement
    const paymentHtml = payment ? `
        <div class="mt-4 bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs font-bold text-green-600 uppercase mb-2">💳 Paiement</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div><span class="text-gray-500">Méthode :</span> <span class="font-semibold">${payment.method || '—'}</span></div>
                <div><span class="text-gray-500">Montant :</span> <span class="font-bold text-orange-500">${parseFloat(payment.amount||0).toLocaleString('fr-FR')} FCFA</span></div>
                <div><span class="text-gray-500">Statut :</span> <span class="font-semibold">${payment.status || '—'}</span></div>
                <div><span class="text-gray-500">Référence :</span> <span class="font-mono text-gray-600">${payment.reference || payment.transaction_id || '—'}</span></div>
            </div>
        </div>` : '';

    document.getElementById('modal-content').innerHTML = `

        <div class="flex items-center justify-between mb-6">
            <span class="text-xs font-bold px-3 py-1.5 rounded-lg ${statusClasses[d.status] || 'bg-gray-100 text-gray-600'}">
                ${statusLabels[d.status] || d.status}
            </span>
            <span class="text-xs text-gray-400">
                #${d.id} · Créé le ${new Date(d.created_at).toLocaleDateString('fr-FR')}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

            <!-- Chauffeur -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-3">👤 Chauffeur</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center
                                text-orange-600 font-bold text-sm flex-shrink-0">
                        ${driverName[0].toUpperCase()}
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">${driverName}</p>
                        <p class="text-gray-500 text-xs">${driverPhone}</p>
                        <p class="text-gray-400 text-xs">${driverEmail}</p>
                    </div>
                </div>
            </div>

            <!-- Véhicule -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-3">🚗 Véhicule</p>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Marque / Modèle</span>
                        <span class="font-semibold text-gray-800">${[brand, model].filter(Boolean).join(' ')}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Immatriculation</span>
                        <span class="font-mono font-bold text-gray-800 bg-gray-200 px-2 py-0.5 rounded">${plate}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Type</span>
                        <span class="font-semibold text-gray-800">${d.vehicle_type || '—'}</span>
                    </div>
                </div>
            </div>

            <!-- Itinéraire -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-3">📍 Itinéraire</p>
                <div class="flex items-start gap-2">
                    <div class="flex flex-col items-center mt-1">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                        <div class="w-0.5 h-6 bg-gray-300"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-orange-400"></div>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-gray-800 text-sm">${d.departure || '—'}</p>
                        <p class="font-bold text-gray-800 text-sm mt-3">${d.destination || '—'}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200 flex gap-6 text-xs">
                    <div>
                        <span class="text-gray-400">Date :</span>
                        <span class="font-semibold ml-1">${d.departure_date || '—'}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Heure :</span>
                        <span class="font-semibold ml-1">${d.departure_time || '—'}</span>
                    </div>
                </div>
            </div>

            <!-- Tarifs & Places -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-3">💰 Tarifs & Places</p>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Prix / place</span>
                        <span class="font-bold text-orange-500 text-sm">
                            ${parseFloat(d.price_per_seat||0).toLocaleString('fr-FR')} FCFA
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Places disponibles</span>
                        <span class="font-bold text-blue-600">${d.available_seats || '—'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bagages inclus / pers.</span>
                        <span class="font-semibold text-gray-700">${luggageInc}</span>
                    </div>
                    ${luggageFee > 0 ? `
                    <div class="flex justify-between">
                        <span class="text-gray-500">Frais bagage excédentaire</span>
                        <span class="font-bold text-orange-500">+${luggageFee.toLocaleString('fr-FR')} FCFA</span>
                    </div>` : ''}
                    <div class="pt-2 border-t border-gray-200 flex justify-between">
                        <span class="text-gray-600 font-semibold">Revenu total</span>
                        <span class="font-bold text-green-600">${totalRevenue.toLocaleString('fr-FR')} FCFA</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Résumé réservations -->
        <div class="grid grid-cols-4 gap-3 mb-4">
            <div class="bg-green-50 border border-green-100 rounded-xl p-3 text-center">
                <p class="text-2xl font-black text-green-600">${confirmed}</p>
                <p class="text-xs text-green-500 font-semibold">Confirmées</p>
            </div>
            <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-3 text-center">
                <p class="text-2xl font-black text-yellow-600">${pendingCount}</p>
                <p class="text-xs text-yellow-500 font-semibold">En attente</p>
            </div>
            <div class="bg-red-50 border border-red-100 rounded-xl p-3 text-center">
                <p class="text-2xl font-black text-red-500">${rejected}</p>
                <p class="text-xs text-red-400 font-semibold">Rejetées</p>
            </div>
            <div class="bg-orange-50 border border-orange-100 rounded-xl p-3 text-center">
                <p class="text-xl font-black text-orange-500">${totalRevenue.toLocaleString('fr-FR')}</p>
                <p class="text-xs text-orange-400 font-semibold">FCFA total</p>
            </div>
        </div>

        <!-- Paiement -->
        ${paymentHtml}

        <!-- Tableau réservations -->
        <div class="border border-gray-100 rounded-xl overflow-hidden mt-4">
            <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-100">
                <p class="text-xs font-bold text-gray-600 uppercase">
                    👥 Réservations clients (${bookings.length})
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-400 uppercase">
                        <tr>
                            <th class="px-3 py-2 text-left">Client</th>
                            <th class="px-3 py-2 text-center">Places</th>
                            <th class="px-3 py-2 text-center">Bagages</th>
                            <th class="px-3 py-2 text-center">Montant</th>
                            <th class="px-3 py-2 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>${bookingRows}</tbody>
                </table>
            </div>
        </div>
    `;
}
</script>
@endpush
