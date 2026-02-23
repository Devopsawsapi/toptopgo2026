@extends('admin.layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    🚗 Détails Chauffeur
</h1>

<div class="grid grid-cols-2 gap-6">

    <!-- INFO PERSONNELLE -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-bold mb-4">Informations personnelles</h2>

        <p><strong>Nom :</strong> {{ $driver->user->first_name }} {{ $driver->user->last_name }}</p>
        <p><strong>Téléphone :</strong> {{ $driver->user->phone }}</p>
        <p><strong>Email :</strong> {{ $driver->user->email }}</p>
        <p><strong>Statut :</strong>
            {{ $driver->user->is_active ? 'Actif' : 'Désactivé' }}
        </p>

        @if($driver->user->avatar)
            <div class="mt-4">
                <p class="font-semibold mb-2">Photo profil</p>
                <img src="{{ asset('storage/'.$driver->user->avatar) }}"
                     class="w-40 rounded shadow">
            </div>
        @endif
    </div>


    <!-- INFO VEHICULE -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-bold mb-4">Véhicule</h2>

        <p><strong>Marque :</strong> {{ $driver->vehicle_brand }}</p>
        <p><strong>Modèle :</strong> {{ $driver->vehicle_model }}</p>
        <p><strong>Année :</strong> {{ $driver->vehicle_year }}</p>
        <p><strong>Couleur :</strong> {{ $driver->vehicle_color }}</p>
        <p><strong>Plaque :</strong> {{ $driver->vehicle_plate_number }}</p>

        @if($driver->vehicle_registration_image)
            <div class="mt-4">
                <p class="font-semibold mb-2">Carte grise</p>
                <img src="{{ asset('storage/'.$driver->vehicle_registration_image) }}"
                     class="w-48 rounded shadow">
            </div>
        @endif

        @if($driver->vehicle_insurance_image)
            <div class="mt-4">
                <p class="font-semibold mb-2">Assurance</p>
                <img src="{{ asset('storage/'.$driver->vehicle_insurance_image) }}"
                     class="w-48 rounded shadow">
            </div>
        @endif
    </div>

</div>

<!-- DOCUMENTS -->
<div class="bg-white p-6 rounded shadow mt-6">
    <h2 class="font-bold mb-4">Documents chauffeur</h2>

    <div class="grid grid-cols-3 gap-6">

        @if($driver->license_image)
        <div>
            <p class="font-semibold mb-2">Permis de conduire</p>
            <img src="{{ asset('storage/'.$driver->license_image) }}"
                 class="rounded shadow">
        </div>
        @endif

        @if($driver->id_card_image)
        <div>
            <p class="font-semibold mb-2">Carte d'identité</p>
            <img src="{{ asset('storage/'.$driver->id_card_image) }}"
                 class="rounded shadow">
        </div>
        @endif

    </div>
</div>

@endsection