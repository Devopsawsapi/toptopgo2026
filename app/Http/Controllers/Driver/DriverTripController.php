<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use App\Services\TripService;
use App\Events\TripStatusUpdated;
use App\Notifications\TripAcceptedNotification;
use App\Notifications\TripCompletedNotification;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;

class DriverTripController extends Controller
{
    public function __construct(private TripService $tripService) {}

    // ── Liste des trajets du chauffeur connecté ──────────────────────
    public function index(Request $request)
    {
        $trips = Trip::with('client', 'payment')
                     ->where('driver_id', $request->user()->id)
                     ->latest()
                     ->paginate(20);

        return TripResource::collection($trips);
    }

    // ── Création d'un trajet ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'departure'         => 'required|string',
            'destination'       => 'required|string',
            'price_per_seat'    => 'required|numeric',
            'available_seats'   => 'required|integer',
            'departure_date'    => 'required|date',
            'departure_time'    => 'required|string',
            'luggage_included'  => 'nullable|integer',
            'extra_luggage_fee' => 'nullable|numeric',
            'vehicle_type'      => 'nullable|string',
        ]);

        $trip = Trip::create([
            'driver_id'         => $request->user()->id,
            'pickup_address'    => $request->departure,
            'dropoff_address'   => $request->destination,
            'departure_city'    => $request->departure,
            'destination_city'  => $request->destination,
            'amount'            => $request->price_per_seat,
            'available_seats'   => $request->available_seats,
            'departure_time'    => $request->departure_date . ' ' . $request->departure_time,
            'luggage_kg'        => $request->luggage_included  ?? 1,
            'vehicle_type'      => $request->vehicle_type,
            'status'            => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trajet publié avec succès.',
            'trip'    => new TripResource($trip),
        ], 201);
    }

    // ── Détail d'un trajet ───────────────────────────────────────────
    public function show(Request $request, $id)
    {
        $trip = Trip::with('client', 'payment')
                    ->where('id', $id)
                    ->where('driver_id', $request->user()->id)
                    ->firstOrFail();

        return new TripResource($trip);
    }

    // ── Mise à jour d'un trajet ──────────────────────────────────────
    public function update(Request $request, $id)
    {
        $trip = Trip::where('id', $id)
                    ->where('driver_id', $request->user()->id)
                    ->firstOrFail();

        $trip->update($request->only([
            'pickup_address', 'dropoff_address',
            'departure_city', 'destination_city',
            'amount', 'available_seats',
            'departure_time', 'luggage_kg',
            'vehicle_type', 'status',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Trajet mis à jour.',
            'trip'    => new TripResource($trip),
        ]);
    }

    // ── Supprimer un trajet ──────────────────────────────────────────
    public function destroy(Request $request, $id)
    {
        $trip = Trip::where('id', $id)
                    ->where('driver_id', $request->user()->id)
                    ->firstOrFail();

        $trip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trajet supprimé.',
        ]);
    }

    // ── Trajets disponibles ──────────────────────────────────────────
    public function available(Request $request)
    {
        $driver = $request->user();

        if ($driver->status !== 'approved') {
            return response()->json(['message' => 'Compte non approuvé.'], 403);
        }

        $trips = Trip::with('client')
                     ->where('status', 'pending')
                     ->latest()
                     ->get();

        return TripResource::collection($trips);
    }

    // ── Accepter un trajet (course VTC) ─────────────────────────────
    public function accept(Request $request, $id)
    {
        $driver = $request->user();

        if ($driver->status !== 'approved') {
            return response()->json(['message' => 'Compte non approuvé.'], 403);
        }

        $trip = Trip::where('id', $id)
                    ->where('status', 'pending')
                    ->firstOrFail();

        $trip->update(['driver_id' => $driver->id, 'status' => 'accepted']);

        $trip->client?->notify(new TripAcceptedNotification($trip->load('driver')));
        TripStatusUpdated::dispatch($trip);

        return response()->json([
            'message' => 'Course acceptée.',
            'trip'    => new TripResource($trip->load('client')),
        ]);
    }

    // ── Démarrer un trajet ───────────────────────────────────────────
    public function start(Request $request, $id)
    {
        $trip = Trip::where('id', $id)
                    ->where('driver_id', $request->user()->id)
                    ->where('status', 'accepted')
                    ->firstOrFail();

        $trip->update(['status' => 'in_progress', 'started_at' => now()]);
        TripStatusUpdated::dispatch($trip);

        return response()->json([
            'message' => 'Course démarrée.',
            'trip'    => new TripResource($trip),
        ]);
    }

    // ── Terminer un trajet ───────────────────────────────────────────
    public function end(Request $request, $id)
    {
        $trip = Trip::where('id', $id)
                    ->where('driver_id', $request->user()->id)
                    ->where('status', 'in_progress')
                    ->firstOrFail();

        $this->tripService->completeTrip($trip);

        $trip->client?->notify(new TripCompletedNotification($trip));
        $request->user()->notify(new TripCompletedNotification($trip));
        TripStatusUpdated::dispatch($trip);

        return response()->json([
            'message' => 'Course terminée.',
            'trip'    => new TripResource($trip),
        ]);
    }

    // ── Liste des réservations reçues par le chauffeur ───────────────
    public function bookings(Request $request)
    {
        $bookings = Booking::with(['trip', 'user'])
            ->whereHas('trip', function ($q) use ($request) {
                $q->where('driver_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $bookings,
            'total'   => $bookings->count(),
        ]);
    }

    // ── Confirmer une réservation ────────────────────────────────────
    public function confirmBooking(Request $request, $id)
    {
        $booking = Booking::with('trip')
            ->whereHas('trip', function ($q) use ($request) {
                $q->where('driver_id', $request->user()->id);
            })
            ->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être confirmée (statut: ' . $booking->status . ').',
            ], 422);
        }

        // Réduire les places disponibles
        $booking->trip->decrement('available_seats', $booking->passengers ?? 1);
        $booking->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée. Le client peut maintenant accepter et payer.',
            'data'    => $booking,
        ]);
    }

    // ── Rejeter une réservation ──────────────────────────────────────
    public function rejectBooking(Request $request, $id)
    {
        $booking = Booking::with('trip')
            ->whereHas('trip', function ($q) use ($request) {
                $q->where('driver_id', $request->user()->id);
            })
            ->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être rejetée.',
            ], 422);
        }

        $booking->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Réservation rejetée.',
        ]);
    }
}