@extends('admin.layouts.app')

@section('content')

<h1 class="text-2xl font-bold mb-6">Créer un Chauffeur</h1>

<div class="bg-white p-6 rounded-lg shadow">

@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        Veuillez corriger les erreurs ci-dessous.
    </div>
@endif

<form method="POST"
      action="{{ route('admin.drivers.store') }}"
      enctype="multipart/form-data">

    @csrf

    <!-- ========================= -->
    <!-- INFORMATIONS PERSONNELLES -->
    <!-- ========================= -->
    <h2 class="text-lg font-semibold mb-4">Informations personnelles</h2>

    <div class="grid grid-cols-2 gap-4 mb-6">

        @include('admin.components.input', ['name' => 'first_name', 'label' => 'Prénom'])
        @include('admin.components.input', ['name' => 'last_name', 'label' => 'Nom'])

        @include('admin.components.input', ['name' => 'date_of_birth', 'label' => 'Date de naissance', 'type' => 'date'])
        @include('admin.components.input', ['name' => 'place_of_birth', 'label' => 'Lieu de naissance'])

        @include('admin.components.input', ['name' => 'phone', 'label' => 'Téléphone'])
        @include('admin.components.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email'])

        @include('admin.components.input', ['name' => 'password', 'label' => 'Mot de passe', 'type' => 'password'])
        @include('admin.components.input', ['name' => 'country', 'label' => 'Pays de résidence'])

        @include('admin.components.image-input', ['name' => 'avatar', 'label' => 'Photo du chauffeur'])

    </div>

    <!-- ========================= -->
    <!-- INFORMATIONS VEHICULE -->
    <!-- ========================= -->
    <h2 class="text-lg font-semibold mb-4">Informations du véhicule</h2>

    <div class="grid grid-cols-2 gap-4 mb-6">

        @include('admin.components.input', ['name' => 'vehicle_brand', 'label' => 'Marque'])
        @include('admin.components.input', ['name' => 'vehicle_model', 'label' => 'Modèle'])

        @include('admin.components.input', ['name' => 'vehicle_color', 'label' => 'Couleur du véhicule'])
        @include('admin.components.input', ['name' => 'seats_available', 'label' => 'Nombre de places', 'type' => 'number'])

        @include('admin.components.input', ['name' => 'vehicle_plate_number', 'label' => 'Immatriculation'])

        @include('admin.components.image-input', ['name' => 'vehicle_registration_image', 'label' => 'Carte grise'])
        @include('admin.components.image-input', ['name' => 'vehicle_insurance_image', 'label' => 'Assurance véhicule'])

    </div>

    <!-- ========================= -->
    <!-- PERMIS -->
    <!-- ========================= -->
    <h2 class="text-lg font-semibold mb-4">Permis de conduire</h2>

    <div class="grid grid-cols-2 gap-4 mb-6">

        @include('admin.components.input', ['name' => 'license_number', 'label' => 'Numéro du permis'])
        @include('admin.components.input', ['name' => 'license_issue_date', 'label' => 'Date d’émission', 'type' => 'date'])

        @include('admin.components.input', ['name' => 'license_expiry', 'label' => 'Date d’expiration', 'type' => 'date'])

        @include('admin.components.image-input', ['name' => 'license_image_recto', 'label' => 'Permis recto'])
        @include('admin.components.image-input', ['name' => 'license_image_verso', 'label' => 'Permis verso'])

    </div>

    <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
        Enregistrer le Chauffeur
    </button>

</form>

</div>

@endsection