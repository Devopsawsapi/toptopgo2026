<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
    /**
     * Liste des trajets pour l'admin avec filtres
     */
    public function index(Request $request)
    {
        $query = Trip::with(['driver', 'client']); // client au lieu de user

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Détails d'un trajet
     */
    public function show($id)
    {
        return response()->json(
            Trip::with(['driver', 'client', 'payment', 'messages'])->findOrFail($id)
        );
    }
}