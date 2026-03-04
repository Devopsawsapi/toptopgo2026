<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class UserTripController extends Controller
{
    // ── Liste des trajets disponibles pour les clients ───────────────
    public function index(Request $request)
    {
        $trips = Trip::with('driver')
            ->where('status', 'pending')
            ->whereDate('departure_date', '>=', now()->toDateString())
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $trips->items(),
            'total'   => $trips->total(),
            'page'    => $trips->currentPage(),
        ]);
    }

    // ── Détail d'un trajet ───────────────────────────────────────────
    public function show($id)
    {
        $trip = Trip::with('driver')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $trip,
        ]);
    }
}