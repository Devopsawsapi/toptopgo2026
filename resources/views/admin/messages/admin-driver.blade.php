@extends('admin.layouts.app')

@section('content')
<div class="p-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🛡 Support Admin ↔ Chauffeurs</h1>
            <p class="text-sm text-gray-500 mt-1">Écrivez à n'importe quel chauffeur depuis cette interface</p>
        </div>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Conversations actives</div>
            <div class="text-3xl font-bold text-blue-600">{{ $totalConversations }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Total Messages envoyés</div>
            <div class="text-3xl font-bold text-green-600">{{ $totalMessages }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Non lus</div>
            <div class="text-3xl font-bold text-orange-500">{{ $unreadMessages }}</div>
        </div>
    </div>

    {{-- ===== RECHERCHE ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.support.drivers.index') }}" class="flex gap-3 items-center">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="🔍 Rechercher par nom ou téléphone..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                Rechercher
            </button>
            <a href="{{ route('admin.support.drivers.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                ✕ Reset
            </a>
        </form>
    </div>

    {{-- ===== CORPS PRINCIPAL ===== --}}
    <div class="flex gap-4" style="height: 65vh;">

        {{-- ---- SIDEBAR : TOUS LES CHAUFFEURS ---- --}}
        <div class="w-1/3 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-700 text-sm">
                    🚗 Tous les chauffeurs
                    <span class="ml-2 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
                        {{ $drivers->total() }}
                    </span>
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Cliquez sur un chauffeur pour lui écrire</p>
            </div>

            <div class="overflow-y-auto flex-1">
                @forelse($drivers as $d)
                    @php
                        $isActive = isset($driver) && $driver->id === $d->id;
                        $hasMsg   = $d->supportMessages->isNotEmpty();
                        $lastMsg  = $d->supportMessages->first();
                        $params   = array_filter(['search' => request('search')]);
                    @endphp

                    <a href="{{ route('admin.support.drivers.show', array_merge(['driver' => $d->id], $params)) }}"
                        class="block p-4 border-b border-gray-50 hover:bg-blue-50 transition
                               {{ $isActive ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}">

                        <div class="flex items-center gap-3">
                            {{-- Avatar --}}
                            <div class="relative flex-shrink-0">
                                @if($d->profile_photo)
                                    <img src="{{ asset('storage/' . $d->profile_photo) }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold
                                        {{ $hasMsg ? 'bg-green-200 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                        {{ strtoupper(substr($d->first_name ?? 'D', 0, 1)) }}
                                    </div>
                                @endif
                                {{-- Point vert si conversation active --}}
                                @if($hasMsg)
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-800 truncate">
                                        {{ $d->first_name }} {{ $d->last_name }}
                                    </span>
                                    @if($d->unread_count > 0)
                                        <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full ml-2 flex-shrink-0">
                                            {{ $d->unread_count }}
                                        </span>
                                    @endif
                                </div>

                                <div class="text-xs text-gray-400 truncate">
                                    {{ $d->phone ?? '—' }}
                                    @if($d->vehicle_brand)
                                        • {{ $d->vehicle_brand }} {{ $d->vehicle_model }}
                                    @endif
                                </div>

                                @if($lastMsg)
                                    <div class="text-xs text-gray-500 mt-0.5 truncate">
                                        {{ \Illuminate\Support\Str::limit($lastMsg->content, 38) }}
                                    </div>
                                    <div class="text-xs text-gray-300">
                                        {{ $lastMsg->created_at->diffForHumans() }}
                                    </div>
                                @else
                                    <div class="text-xs text-gray-300 mt-0.5 italic">
                                        Aucun message — cliquez pour écrire
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        <div class="text-4xl mb-2">🚗</div>
                        <p class="text-sm">Aucun chauffeur trouvé</p>
                    </div>
                @endforelse
            </div>

            @if($drivers->hasPages())
                <div class="p-3 border-t border-gray-100 text-center">
                    {{ $drivers->appends(request()->query())->links('pagination::simple-tailwind') }}
                </div>
            @endif
        </div>

        {{-- ---- ZONE MESSAGES ---- --}}
        <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">

            @if(isset($driver) && isset($messages))

                {{-- Header --}}
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($driver->profile_photo)
                            <img src="{{ asset('storage/' . $driver->profile_photo) }}"
                                 class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-green-200 text-green-800 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($driver->first_name ?? 'D', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="font-semibold text-gray-800">
                                {{ $driver->first_name }} {{ $driver->last_name }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $driver->phone ?? '—' }}
                                @if($driver->vehicle_brand)
                                    • {{ $driver->vehicle_brand }} {{ $driver->vehicle_model }}
                                @endif
                                • {{ $messages->count() }} message(s)
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 text-xs">
                        @if($driver->phone)
                            <span class="bg-green-50 text-green-700 px-2 py-1 rounded">
                                📱 {{ $driver->phone }}
                            </span>
                        @endif
                        @if($driver->vehicle_plate)
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                🚗 {{ $driver->vehicle_plate }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Liste messages --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50" id="messagesBox">
                    @forelse($messages as $message)
                        <div class="flex justify-end items-end gap-2">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="text-xs text-gray-400 mb-1 text-right">
                                    🛡 {{ $message->admin->name ?? session('admin_name', 'Admin') }}
                                </div>
                                <div class="px-4 py-2.5 rounded-2xl rounded-tr-none text-sm leading-relaxed bg-blue-600 text-white shadow-sm">
                                    {{ $message->content }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1 text-right">
                                    {{ $message->created_at->format('d/m H:i') }}
                                    @if($message->is_read)
                                        <span class="text-blue-400 ml-1">✓✓ Lu</span>
                                    @else
                                        <span class="text-gray-300 ml-1">✓ Envoyé</span>
                                    @endif
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-yellow-400 text-black flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr(session('admin_name', 'A'), 0, 1)) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 py-10">
                            <div class="text-4xl mb-3">✉️</div>
                            <p class="font-medium text-gray-500">Démarrez la conversation</p>
                            <p class="text-sm mt-1">
                                Écrivez votre premier message à
                                <span class="font-semibold text-green-600">{{ $driver->first_name }}</span>
                                ci-dessous
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Formulaire envoi --}}
                <div class="p-4 border-t border-gray-100 bg-white">
                    <form method="POST" action="{{ route('admin.support.drivers.send', $driver->id) }}"
                          class="flex gap-3 items-end">
                        @csrf
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <div class="flex-1">
                            <textarea name="content" rows="2"
                                placeholder="Écrire un message à {{ $driver->first_name }}..."
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm resize-none
                                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required></textarea>
                        </div>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition flex-shrink-0">
                            Envoyer ➤
                        </button>
                    </form>
                </div>

            @else
                <div class="flex-1 flex items-center justify-center text-gray-400">
                    <div class="text-center">
                        <div class="text-6xl mb-4">🚗</div>
                        <p class="text-lg font-medium text-gray-500">Sélectionnez un chauffeur</p>
                        <p class="text-sm mt-1">Cliquez sur n'importe quel chauffeur dans la liste pour lui écrire</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    const box = document.getElementById('messagesBox');
    if (box) box.scrollTop = box.scrollHeight;

    @if(isset($driver))
    setInterval(() => location.reload(), 10000);
    @endif
</script>
@endsection