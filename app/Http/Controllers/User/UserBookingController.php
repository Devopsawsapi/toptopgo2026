<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBookingController extends Controller
{
    // ── Liste des réservations du client connecté ────────────────────
    public function index()
    {
        $bookings = Booking::with(['trip.driver'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $bookings,
            'total'   => $bookings->count(),
        ]);
    }

    // ── Détail d'une réservation ─────────────────────────────────────
    public function show($id)
    {
        $booking = Booking::with(['trip.driver'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $booking,
        ]);
    }

    // ── Créer une réservation ────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
        ]);

        // Vérifier si déjà réservé
        $exists = Booking::where('user_id', Auth::id())
            ->where('trip_id', $request->trip_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà réservé ce trajet.',
            ], 422);
        }

        $booking = Booking::create([
            'user_id'   => Auth::id(),
            'trip_id'   => $request->trip_id,
            'status'    => 'pending',
            'booked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réservation effectuée avec succès.',
            'data'    => $booking->load('trip.driver'),
        ], 201);
    }

    // ── Annuler une réservation ──────────────────────────────────────
    public function cancel($id)
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut plus être annulée.',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée avec succès.',
        ]);
    }
}