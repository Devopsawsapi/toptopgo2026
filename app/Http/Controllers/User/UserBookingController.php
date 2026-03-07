<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBookingController extends Controller
{
    // ── Liste des réservations du client ─────────────────────────────
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

        return response()->json(['success' => true, 'data' => $booking]);
    }

    /**
     * ✅ FIX store() :
     * - Sauvegarde seats (nombre de places réservées)
     * - Sauvegarde amount (prix total)
     * - Décrémente available_seats sur le Trip
     * - Vérifie qu'il y a assez de places disponibles
     */
    public function store(Request $request)
    {
        $request->validate([
            'trip_id'     => 'required|exists:trips,id',
            'seats_booked'=> 'nullable|integer|min:1|max:20',
            'extra_bags'  => 'nullable|integer|min:0',
            'total_price' => 'nullable|numeric|min:0',
        ]);

        $seats = max(1, (int) ($request->seats_booked ?? 1));

        // Vérifier si déjà réservé
        $exists = Booking::where('user_id', Auth::id())
            ->where('trip_id', $request->trip_id)
            ->whereIn('status', ['pending', 'confirmed', 'paid'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà réservé ce trajet.',
            ], 422);
        }

        // ✅ Vérifier places disponibles
        $trip = Trip::findOrFail($request->trip_id);

        if ($trip->available_seats < $seats) {
            return response()->json([
                'success' => false,
                'message' => "Seulement {$trip->available_seats} place(s) disponible(s).",
            ], 422);
        }

        // Calcul montant
        $pricePerSeat = (float) ($trip->price_per_seat ?? $trip->amount ?? 0);
        $extraFee     = (float) ($request->total_price ?? 0) - ($pricePerSeat * $seats);
        $totalAmount  = (float) ($request->total_price ?? $pricePerSeat * $seats);

        // ✅ Créer la réservation avec seats + amount
        $booking = Booking::create([
            'user_id'    => Auth::id(),
            'trip_id'    => $request->trip_id,
            'seats'      => $seats,          // ✅ nombre de places
            'passengers' => $seats,          // alias selon votre migration
            'amount'     => $totalAmount,    // ✅ montant total
            'extra_bags' => $request->extra_bags ?? 0,
            'extra_fee'  => max(0, $extraFee),
            'status'     => 'pending',
            'booked_at'  => now(),
        ]);

        // ✅ Décrémenter les places disponibles sur le trajet
        $trip->decrement('available_seats', $seats);

        return response()->json([
            'success' => true,
            'message' => 'Réservation effectuée avec succès.',
            'booking' => $booking->load('trip.driver'),
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

        // ✅ Remettre les places disponibles
        Trip::where('id', $booking->trip_id)
            ->increment('available_seats', (int) ($booking->seats ?? $booking->passengers ?? 1));

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée avec succès.',
        ]);
    }

    // ── Accepter / Rejeter (si workflow double confirmation) ─────────
    public function accept($id)
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);
        $booking->update(['status' => 'confirmed']);
        return response()->json(['success' => true, 'data' => $booking]);
    }

    public function reject($id)
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);
        $booking->update(['status' => 'rejected']);
        Trip::where('id', $booking->trip_id)
            ->increment('available_seats', (int) ($booking->seats ?? $booking->passengers ?? 1));
        return response()->json(['success' => true]);
    }
}
