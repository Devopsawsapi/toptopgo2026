@extends('layouts.admin')

@section('content')

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 Trajets & Courses</h1>
            <p class="text-sm text-gray-500 mt-1">
                Tous les trajets créés par les chauffeurs
            </p>
        </div>

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


{{-- TABLEAU --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

<div class="px-6 py-4 border-b border-gray-100">
<h2 class="font-bold text-gray-700 text-sm uppercase">
📋 Liste des trajets
<span class="ml-2 bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">
{{ $trips->total() }} résultats
</span>
</h2>
</div>


<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-xs text-gray-500 uppercase">

<tr>
<th class="px-4 py-3 text-left">#</th>
<th class="px-4 py-3 text-left">Chauffeur</th>
<th class="px-4 py-3 text-left">Itinéraire</th>
<th class="px-4 py-3 text-left">Date</th>
<th class="px-4 py-3 text-left">Prix</th>
<th class="px-4 py-3 text-left">Statut</th>
<th class="px-4 py-3 text-center">Actions</th>
</tr>

</thead>

<tbody class="divide-y divide-gray-50">

@forelse($trips as $trip)

@php

$driver=$trip->driver;

@endphp

<tr class="hover:bg-gray-50">

<td class="px-4 py-3 text-gray-400 font-mono text-xs">
#{{ $trip->id }}
</td>


<td class="px-4 py-3">

<div>

<p class="font-semibold text-gray-800 text-xs">
{{ $driver?->name ?? 'N/A' }}
</p>

<p class="text-gray-400 text-xs">
{{ $driver?->phone ?? '-' }}
</p>

</div>

</td>


<td class="px-4 py-3">

<p class="text-xs font-semibold text-gray-700">
{{ $trip->departure }}
</p>

<p class="text-xs text-gray-500">
{{ $trip->destination }}
</p>

</td>


<td class="px-4 py-3 text-xs text-gray-600">

{{ $trip->departure_date }}

</td>


<td class="px-4 py-3">

<span class="text-orange-500 font-bold text-sm">

{{ number_format($trip->price_per_seat ?? 0,0,'.',' ') }}

FCFA

</span>

</td>


<td class="px-4 py-3">

<span class="text-xs font-bold px-2 py-1 rounded-lg bg-gray-100 text-gray-600">

{{ $trip->status }}

</span>

</td>


<td class="px-4 py-3 text-center">

<button onclick="openTripModal({{ $trip->id }})"
class="bg-blue-500 text-white text-xs px-3 py-1.5 rounded-lg">

Voir

</button>

</td>

</tr>

@empty

<tr>

<td colspan="7" class="px-6 py-16 text-center">

🚗 Aucun trajet trouvé

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

<div class="px-6 py-4 border-t border-gray-100">

{{ $trips->links() }}

</div>

</div>

</div>


{{-- MODAL --}}

<div id="trip-modal"
class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">

<div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6">

<div class="flex justify-between mb-4">

<h2 class="font-bold">Détail du trajet</h2>

<button onclick="closeTripModal()">X</button>

</div>


<div id="modal-content">

Chargement...

</div>

</div>

</div>

@endsection


@push('scripts')

<script>

function openTripModal(id){

document.getElementById('trip-modal').classList.remove('hidden')

fetch(`/admin/trips/${id}/detail`)

.then(res=>res.json())

.then(data=>{

document.getElementById('modal-content').innerHTML=`

<p><b>Départ :</b> ${data.departure}</p>

<p><b>Destination :</b> ${data.destination}</p>

<p><b>Prix :</b> ${data.price_per_seat} FCFA</p>

<p><b>Status :</b> ${data.status}</p>

`

})

}


function closeTripModal(){

document.getElementById('trip-modal').classList.add('hidden')

}

</script>

@endpush