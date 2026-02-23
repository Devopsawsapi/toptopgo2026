@extends('admin.layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">🚗 Chauffeurs</h1>

    <a href="{{ route('admin.drivers.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">
        + Nouveau Chauffeur
    </a>
</div>

<div class="bg-white shadow rounded-lg p-6 overflow-x-auto">

<table class="w-full text-sm">
    <thead>
        <tr class="bg-gray-100 text-gray-700">
            <th class="p-3 text-left">Nom</th>
            <th class="p-3 text-left">Téléphone</th>
            <th class="p-3 text-left">Véhicule</th>
            <th class="p-3 text-left">Plaque</th>
            <th class="p-3 text-left">Couleur</th>
            <th class="p-3 text-left">Statut</th>
            <th class="p-3 text-left">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($drivers as $driver)
        <tr class="border-b hover:bg-gray-50 transition">

            <td class="p-3 font-semibold">
                {{ optional($driver->user)->first_name }}
                {{ optional($driver->user)->last_name }}
            </td>

            <td class="p-3">
                {{ optional($driver->user)->phone }}
            </td>

            <td class="p-3">
                {{ $driver->vehicle_brand }} {{ $driver->vehicle_model }}
            </td>

            <td class="p-3 font-mono">
                {{ $driver->vehicle_plate_number }}
            </td>

            <td class="p-3">
                {{ $driver->vehicle_color }}
            </td>

            <td class="p-3">
                @if(optional($driver->user)->is_active)
                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">
                        Actif
                    </span>
                @else
                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">
                        Désactivé
                    </span>
                @endif
            </td>

            <td class="p-3 flex gap-4 items-center">

                <a href="{{ route('admin.drivers.show', $driver) }}"
                   class="text-blue-600 hover:underline">
                   Détails
                </a>

                <form method="POST"
                      action="{{ route('admin.drivers.toggle-status', $driver) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                        class="{{ optional($driver->user)->is_active ? 'text-red-600 hover:underline' : 'text-green-600 hover:underline' }}">
                        {{ optional($driver->user)->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>

            </td>

        </tr>
        @empty
        <tr>
            <td colspan="7" class="p-6 text-center text-gray-500">
                Aucun chauffeur enregistré.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-6">
    {{ $drivers->links() }}
</div>

</div>

@endsection