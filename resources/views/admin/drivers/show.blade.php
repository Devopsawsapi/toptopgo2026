@extends('admin.layouts.app')

@section('content')

<div class="max-w-5xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.drivers.index') }}" class="text-gray-400 hover:text-gray-700 transition text-2xl">←</a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">👤 Profil Chauffeur</h1>
                <p class="text-gray-500 text-sm mt-1">{{ $driver->first_name }} {{ $driver->last_name }}</p>
            </div>
        </div>
        <a href="{{ route('admin.drivers.edit', $driver->id) }}"
           class="bg-[#1DA1F2] text-white px-6 py-3 rounded-xl font-semibold hover:bg-[#FFC107] hover:text-black transition-all duration-300">
            ✏️ Modifier
        </a>
    </div>

    <!-- INFO PERSO -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <div class="flex items-center gap-6 mb-6">
            @if($driver->profile_photo)
                {{-- ✅ L'accessor retourne directement l'URL complète Backblaze --}}
                <img src="{{ $driver->profile_photo }}"
                     class="w-20 h-20 rounded-full object-cover border-4 border-[#1DA1F2]"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="w-20 h-20 rounded-full bg-[#1DA1F2] items-center justify-center text-3xl font-bold text-white hidden">
                    {{ strtoupper(substr($driver->first_name, 0, 1)) }}
                </div>
            @else
                <div class="w-20 h-20 rounded-full bg-[#1DA1F2] flex items-center justify-center text-3xl font-bold text-white">
                    {{ strtoupper(substr($driver->first_name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $driver->first_name }} {{ $driver->last_name }}</h2>
                <p class="text-gray-500">{{ $driver->phone }}</p>
                <div class="flex gap-2 mt-2">
                    @if($driver->status == 'approved')
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">✅ Approuvé</span>
                    @elseif($driver->status == 'pending')
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">⏳ En attente KYC</span>
                    @elseif($driver->status == 'rejected')
                        <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">❌ Rejeté</span>
                    @else
                        <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1 rounded-full">🚫 Suspendu</span>
                    @endif
                    @if($driver->driver_status == 'online')
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">🟢 En ligne</span>
                    @else
                        <span class="bg-gray-100 text-gray-500 text-xs font-semibold px-3 py-1 rounded-full">⚫ Hors ligne</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Date de naissance</p>
                <p class="font-semibold text-gray-800">{{ $driver->birth_date ? \Carbon\Carbon::parse($driver->birth_date)->format('d/m/Y') : '—' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Lieu de naissance</p>
                <p class="font-semibold text-gray-800">{{ $driver->birth_place ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Pays de naissance</p>
                <p class="font-semibold text-gray-800">{{ $driver->country_birth ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Inscrit le</p>
                <p class="font-semibold text-gray-800">{{ $driver->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Ville</p>
                <p class="font-semibold text-gray-800">{{ $driver->vehicle_city ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Pays</p>
                <p class="font-semibold text-gray-800">{{ $driver->vehicle_country ?? '—' }}</p>
            </div>
        </div>
    </div>

    <!-- VÉHICULE -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-700 mb-4 pb-3 border-b border-gray-100">🚗 Véhicule</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Plaque</p>
                <p class="font-semibold text-gray-800">{{ $driver->vehicle_plate ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Marque / Modèle</p>
                <p class="font-semibold text-gray-800">{{ $driver->vehicle_brand ?? '—' }} {{ $driver->vehicle_model ?? '' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Type</p>
                <p class="font-semibold text-gray-800">{{ $driver->vehicle_type ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Couleur</p>
                <p class="font-semibold text-gray-800">{{ $driver->vehicle_color ?? '—' }}</p>
            </div>
        </div>
    </div>

    <!-- DOCUMENTS KYC -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">📄 Documents KYC</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @php
            $docs = [
                ['label' => '🪪 CNI Recto',      'field' => 'id_card_front'],
                ['label' => '🪪 CNI Verso',       'field' => 'id_card_back'],
                ['label' => '📋 Permis Recto',    'field' => 'license_front'],
                ['label' => '📋 Permis Verso',    'field' => 'license_back'],
                ['label' => '🚗 Carte grise',     'field' => 'vehicle_registration'],
                ['label' => '🛡 Assurance',       'field' => 'insurance'],
            ];
            @endphp

            @foreach($docs as $doc)
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <p class="text-sm font-semibold text-gray-700">{{ $doc['label'] }}</p>
                </div>
                <div class="p-3">
                    @if($driver->{$doc['field']})
                        @php
                            {{-- ✅ L'accessor retourne l'URL complète — on extrait juste l'extension --}}
                            $fileUrl = $driver->{$doc['field']};
                            $ext = strtolower(pathinfo(parse_url($fileUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
                        @endphp
                        @if(in_array($ext, ['jpg','jpeg','png','webp']))
                            <a href="{{ $fileUrl }}" target="_blank">
                                <img src="{{ $fileUrl }}"
                                     class="w-full h-32 object-cover rounded-lg hover:opacity-90 transition cursor-pointer"
                                     onerror="this.parentElement.innerHTML='<div class=\'h-32 bg-red-50 rounded-lg flex items-center justify-center text-red-400 text-sm\'>Image non accessible</div>'">
                            </a>
                        @else
                            <a href="{{ $fileUrl }}" target="_blank"
                               class="flex items-center gap-2 text-[#1DA1F2] hover:underline text-sm">
                                📎 Voir le fichier
                            </a>
                        @endif
                    @else
                        <div class="h-32 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-sm">
                            Non fourni
                        </div>
                    @endif
                </div>
            </div>
            @endforeach

        </div>
    </div>

    <!-- ACTIONS -->
    <div class="flex gap-4 mb-10 flex-wrap">
        @if($driver->status == 'pending')
            <form method="POST" action="{{ route('admin.drivers.approve', $driver->id) }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-xl font-semibold hover:bg-green-600 transition">
                    ✅ Approuver le chauffeur
                </button>
            </form>
            <form method="POST" action="{{ route('admin.drivers.reject', $driver->id) }}" class="flex-1">
                @csrf
                <button type="submit" onclick="return confirm('Rejeter ce chauffeur ?')"
                        class="w-full bg-red-100 text-red-700 py-3 rounded-xl font-semibold hover:bg-red-200 transition">
                    ❌ Rejeter
                </button>
            </form>
        @elseif($driver->status == 'approved')
            <form method="POST" action="{{ route('admin.drivers.suspend', $driver->id) }}" class="flex-1">
                @csrf
                <button type="submit" onclick="return confirm('Suspendre ce chauffeur ?')"
                        class="w-full bg-orange-100 text-orange-700 py-3 rounded-xl font-semibold hover:bg-orange-200 transition">
                    🚫 Suspendre
                </button>
            </form>
        @elseif($driver->status == 'suspended')
            <form method="POST" action="{{ route('admin.drivers.activate', $driver->id) }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full bg-green-100 text-green-700 py-3 rounded-xl font-semibold hover:bg-green-200 transition">
                    ✅ Réactiver
                </button>
            </form>
        @endif

        <form method="POST" action="{{ route('admin.drivers.destroy', $driver->id) }}" class="flex-1">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Supprimer définitivement ce chauffeur ?')"
                    class="w-full bg-red-100 text-red-700 py-3 rounded-xl font-semibold hover:bg-red-200 transition">
                🗑 Supprimer
            </button>
        </form>
    </div>

</div>
@endsection