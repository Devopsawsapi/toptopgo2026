@extends('admin.layouts.app')

@section('content')
<div class="p-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📊 Gestion des Commissions</h1>
            <p class="text-sm text-gray-500 mt-1">Définissez les taux par pays, type de véhicule ou chauffeur</p>
        </div>
        <a href="{{ route('admin.commission-rates.export', request()->query()) }}"
            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
            ⬇️ Exporter CSV
        </a>
    </div>

    {{-- ===== RÈGLES DE COMMISSION ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

        {{-- ---- Règles existantes ---- --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-base font-bold text-gray-800 mb-4">⚙️ Règles actives</h2>

            {{-- Priorité --}}
            <div class="bg-blue-50 rounded-lg p-3 mb-4 text-xs text-blue-700">
                <strong>Priorité d'application :</strong>
                👤 Chauffeur > 🚗 Type véhicule > 🌍 Pays > 🌐 Global
            </div>

            <div class="space-y-2">
                @foreach($allRates as $rate)
                <div class="flex items-center justify-between p-3 rounded-lg border
                    {{ $rate->is_active ? 'border-gray-200 bg-white' : 'border-gray-100 bg-gray-50 opacity-60' }}">

                    <div class="flex items-center gap-3">
                        {{-- Badge type --}}
                        <span class="text-xs px-2 py-1 rounded-full font-medium
                            {{ $rate->type === 'global'       ? 'bg-gray-200 text-gray-700' : '' }}
                            {{ $rate->type === 'country'      ? 'bg-green-100 text-green-700' : '' }}
                            {{ $rate->type === 'vehicle_type' ? 'bg-purple-100 text-purple-700' : '' }}
                            {{ $rate->type === 'driver'       ? 'bg-indigo-100 text-indigo-700' : '' }}">
                            {{ $rate->type_label }}
                        </span>
                        @if($rate->description)
                            <span class="text-xs text-gray-400">{{ $rate->description }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold {{ $rate->is_active ? 'text-blue-600' : 'text-gray-400' }}">
                            {{ $rate->rate }}%
                        </span>

                        {{-- Toggle actif/inactif --}}
                        <form method="POST" action="{{ route('admin.commission-rates.update', $rate->id) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="rate" value="{{ $rate->rate }}">
                            <input type="hidden" name="description" value="{{ $rate->description }}">
                            <input type="hidden" name="is_active" value="{{ $rate->is_active ? 0 : 1 }}">
                            <button type="submit"
                                class="text-xs px-2 py-1 rounded transition
                                    {{ $rate->is_active ? 'bg-green-100 text-green-700 hover:bg-red-100 hover:text-red-700' : 'bg-gray-100 text-gray-500 hover:bg-green-100 hover:text-green-700' }}">
                                {{ $rate->is_active ? '✓ Actif' : '✗ Inactif' }}
                            </button>
                        </form>

                        {{-- Supprimer (sauf global) --}}
                        @if($rate->type !== 'global')
                        <form method="POST" action="{{ route('admin.commission-rates.destroy', $rate->id) }}"
                              onsubmit="return confirm('Supprimer cette règle ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 text-sm transition">✕</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ---- Formulaire ajout règle ---- --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-base font-bold text-gray-800 mb-4">➕ Ajouter / Modifier une règle</h2>

            <form method="POST" action="{{ route('admin.commission-rates.store') }}" class="space-y-4" id="rateForm">
                @csrf

                {{-- Type de règle --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Type de règle</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['global' => '🌐 Global', 'country' => '🌍 Par pays', 'vehicle_type' => '🚗 Par véhicule', 'driver' => '👤 Par chauffeur'] as $val => $label)
                            <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-blue-50 transition
                                {{ old('type') === $val ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                                <input type="radio" name="type" value="{{ $val }}"
                                       {{ old('type', 'global') === $val ? 'checked' : '' }}
                                       class="text-blue-600" onchange="toggleFields(this.value)">
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Champ pays --}}
                <div id="field_country" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">🌍 Pays</label>
                    <select name="country" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner un pays</option>
                        @foreach($countries as $c)
                            <option value="{{ $c }}" {{ old('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                        <option value="__custom__">Autre pays (saisie libre)</option>
                    </select>
                    <input type="text" name="country_custom" placeholder="Nom du pays..."
                           class="w-full mt-2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hidden"
                           id="country_custom_input">
                </div>

                {{-- Champ type véhicule --}}
                <div id="field_vehicle_type" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">🚗 Type de véhicule</label>
                    <select name="vehicle_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner un type</option>
                        @foreach(['Standard', 'Confort', 'Van', 'PMR'] as $type)
                            <option value="{{ $type }}" {{ old('vehicle_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Champ chauffeur --}}
                <div id="field_driver" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">👤 Chauffeur</label>
                    <select name="driver_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner un chauffeur</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ old('driver_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->first_name }} {{ $d->last_name }}
                                @if($d->phone) — {{ $d->phone }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Taux + description --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Taux (%)</label>
                        <input type="number" name="rate" step="0.01" min="0" max="100"
                               value="{{ old('rate') }}" placeholder="Ex: 15.00"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               placeholder="Ex: Taux spécial Gabon"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-medium transition">
                    ✓ Enregistrer la règle
                </button>
            </form>
        </div>
    </div>

    {{-- ===== FILTRES PÉRIODE ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" action="{{ route('admin.commission-rates.index') }}" class="space-y-4">

            <div class="flex gap-2 flex-wrap">
                @foreach(['day' => "Aujourd'hui", 'week' => 'Cette semaine', 'month' => 'Ce mois', 'year' => 'Cette année', 'custom' => 'Personnalisé'] as $key => $label)
                    <button type="submit" name="period" value="{{ $key }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition
                               {{ $period === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if($period === 'custom')
            <div class="flex gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Du</label>
                    <input type="date" name="start" value="{{ request('start', $startDate->toDateString()) }}"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Au</label>
                    <input type="date" name="end" value="{{ request('end', $endDate->toDateString()) }}"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Appliquer</button>
            </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">🚗 Chauffeur</label>
                    <select name="driver_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->first_name }} {{ $d->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">🌍 Pays</label>
                    <select name="country" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        @foreach($countries as $c)
                            <option value="{{ $c }}" {{ request('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">🏙️ Ville</label>
                    <select name="city" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Toutes</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">🚙 Type véhicule</label>
                    <select name="vehicle_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        @foreach(['Standard', 'Confort', 'Van', 'PMR'] as $type)
                            <option value="{{ $type }}" {{ request('vehicle_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <input type="hidden" name="period" value="{{ $period }}">
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                    🔍 Filtrer
                </button>
                <a href="{{ route('admin.commission-rates.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm transition">
                    ✕ Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Total Courses</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($totalTrips) }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $startDate->format('d/m') }} → {{ $endDate->format('d/m/Y') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Revenus Bruts</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-400">XAF</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-blue-500">
            <div class="text-sm text-gray-500 mb-1">Commission TopTopGo</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($totalCommission, 0, ',', ' ') }}</div>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-gray-400">XAF</span>
                @if($commissionEvolution != 0)
                    <span class="text-xs font-semibold {{ $commissionEvolution > 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $commissionEvolution > 0 ? '▲' : '▼' }} {{ abs($commissionEvolution) }}%
                    </span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-green-500">
            <div class="text-sm text-gray-500 mb-1">Net Chauffeurs</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($totalDriverNet, 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-400">XAF</div>
        </div>
    </div>

    {{-- ===== GRAPHIQUE + TOP ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">📈 Évolution des commissions</h3>
            @if($dailyStats->isEmpty())
                <div class="text-center text-gray-400 py-12">
                    <div class="text-4xl mb-2">📊</div>
                    <p>Aucune donnée sur cette période</p>
                </div>
            @else
                <canvas id="commissionChart" height="220"></canvas>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">🏆 Top 10 chauffeurs</h3>
            @if($topDrivers->isEmpty())
                <div class="text-center text-gray-400 py-12">
                    <div class="text-4xl mb-2">🚗</div>
                    <p>Aucune donnée</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($topDrivers as $i => $td)
                        @php $maxC = $topDrivers->first()->total_commission ?: 1; @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                                {{ $i === 0 ? 'bg-yellow-400 text-black' : ($i === 1 ? 'bg-gray-300 text-black' : ($i === 2 ? 'bg-orange-400 text-white' : 'bg-gray-100 text-gray-500')) }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-800 truncate">
                                    {{ $td->driver->first_name ?? '—' }} {{ $td->driver->last_name ?? '' }}
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                                    <div class="bg-blue-500 h-1.5 rounded-full"
                                         style="width: {{ min(100, ($td->total_commission / $maxC) * 100) }}%"></div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-sm font-bold text-blue-600">{{ number_format($td->total_commission, 0, ',', ' ') }} XAF</div>
                                <div class="text-xs text-gray-400">{{ $td->trips_count }} courses</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ===== TABLEAU COURSES ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-700">
                📋 Détail des courses
                <span class="ml-2 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">{{ $trips->total() }}</span>
            </h3>
            <span class="text-xs text-gray-400">{{ $startDate->format('d/m/Y') }} → {{ $endDate->format('d/m/Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Chauffeur</th>
                        <th class="px-4 py-3 text-left">Ville / Pays</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                        <th class="px-4 py-3 text-right">Commission</th>
                        <th class="px-4 py-3 text-right">Net chauffeur</th>
                        <th class="px-4 py-3 text-center">Taux</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($trips as $trip)
                        @php $taux = $trip->amount > 0 ? round(($trip->commission / $trip->amount) * 100, 1) : 0; @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-400 text-xs">#{{ $trip->id }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $trip->completed_at?->format('d/m/Y') }}<br>
                                <span class="text-gray-400">{{ $trip->completed_at?->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $trip->driver->first_name ?? '—' }} {{ $trip->driver->last_name ?? '' }}</div>
                                <div class="text-xs text-gray-400">{{ $trip->driver->phone ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $trip->driver->vehicle_city ?? '—' }}<br>
                                <span class="text-gray-400">{{ $trip->driver->vehicle_country ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">{{ $trip->vehicle_type }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format($trip->amount, 0, ',', ' ') }} XAF</td>
                            <td class="px-4 py-3 text-right font-bold text-blue-600">{{ number_format($trip->commission, 0, ',', ' ') }} XAF</td>
                            <td class="px-4 py-3 text-right text-green-600">{{ number_format($trip->driver_net, 0, ',', ' ') }} XAF</td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $taux }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                <div class="text-4xl mb-2">📋</div>
                                <p>Aucune course sur cette période</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($trips->count() > 0)
                <tfoot class="bg-gray-50 font-semibold border-t-2 border-gray-200">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-gray-600 text-sm">TOTAL période</td>
                        <td class="px-4 py-3 text-right text-gray-800">{{ number_format($totalRevenue, 0, ',', ' ') }} XAF</td>
                        <td class="px-4 py-3 text-right text-blue-600">{{ number_format($totalCommission, 0, ',', ' ') }} XAF</td>
                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($totalDriverNet, 0, ',', ' ') }} XAF</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($trips->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $trips->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
@if($dailyStats->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('commissionChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! $dailyStats->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toJson() !!},
        datasets: [
            { label: 'Commission (XAF)', data: {!! $dailyStats->pluck('commission')->toJson() !!}, backgroundColor: 'rgba(59,130,246,0.8)', borderRadius: 4 },
            { label: 'Revenu brut (XAF)', data: {!! $dailyStats->pluck('revenue')->toJson() !!}, backgroundColor: 'rgba(209,213,219,0.6)', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' XAF' } } }
    }
});
</script>
@endif

<script>
function toggleFields(type) {
    document.getElementById('field_country').classList.add('hidden');
    document.getElementById('field_vehicle_type').classList.add('hidden');
    document.getElementById('field_driver').classList.add('hidden');
    if (type === 'country')      document.getElementById('field_country').classList.remove('hidden');
    if (type === 'vehicle_type') document.getElementById('field_vehicle_type').classList.remove('hidden');
    if (type === 'driver')       document.getElementById('field_driver').classList.remove('hidden');
}

// Afficher le bon champ au chargement
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="type"]:checked');
    if (checked) toggleFields(checked.value);

    // Gestion pays custom
    document.querySelector('select[name="country"]')?.addEventListener('change', function() {
        const custom = document.getElementById('country_custom_input');
        if (this.value === '__custom__') {
            custom.classList.remove('hidden');
            custom.name = 'country';
            this.name = 'country_select';
        } else {
            custom.classList.add('hidden');
            custom.name = 'country_custom';
            this.name = 'country';
        }
    });
});
</script>
@endsection