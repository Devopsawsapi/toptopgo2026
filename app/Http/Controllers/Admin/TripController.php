<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    /**
     * GET /admin/trips
     * FIX: retourne TOUS les trajets (tous statuts) avec stats complètes
     */
    public function index(Request $request)
    {
        $query = Trip::with(['driver'])
            ->withCount('bookings');

        // FIX: filtre statut optionnel (pas forcé à 'pending')
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filtres recherche
        if ($request->pickup) {
            $query->where(function($q) use ($request) {
                $q->where('pickup_address', 'like', '%'.$request->pickup.'%')
                  ->orWhere('departure', 'like', '%'.$request->pickup.'%')
                  ->orWhere('departure_city', 'like', '%'.$request->pickup.'%');
            });
        }
        if ($request->dropoff) {
            $query->where(function($q) use ($request) {
                $q->where('dropoff_address', 'like', '%'.$request->dropoff.'%')
                  ->orWhere('destination', 'like', '%'.$request->dropoff.'%')
                  ->orWhere('destination_city', 'like', '%'.$request->dropoff.'%');
            });
        }
        if ($request->date) {
            $query->where('departure_date', $request->date);
        }
        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        $trips = $query->orderBy('created_at', 'desc')->get();

        // FIX: corriger les anciens trajets avec price_per_seat=0
        // (synchronise amount ↔ price_per_seat à la volée)
        $trips->each(function($t) {
            if ((float)$t->price_per_seat === 0.0 && (float)$t->amount > 0) {
                $t->price_per_seat = $t->amount;
            } elseif ((float)$t->amount === 0.0 && (float)$t->price_per_seat > 0) {
                $t->amount = $t->price_per_seat;
            }
        });

        // FIX: stats globales pour le dashboard admin
        $stats = [
            'total'       => Trip::count(),
            'pending'     => Trip::where('status', 'pending')->count(),
            'in_progress' => Trip::where('status', 'in_progress')->count(),
            'completed'   => Trip::where('status', 'completed')->count(),
            'cancelled'   => Trip::whereIn('status', ['cancelled','rejected'])->count(),
            'total_revenue' => (float) Booking::whereIn('status', ['confirmed','paid','completed'])
                                ->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'total'   => $trips->count(),
            'data'    => $trips->map(fn($t) => $this->formatTrip($t)),
        ]);
    }

    /**
     * GET /admin/trips/{id}
     */
    public function show($id)
    {
        $trip = Trip::with(['driver'])->withCount('bookings')->find($id);

        if (!$trip) {
            return response()->json(['success' => false, 'message' => 'Trajet introuvable'], 404);
        }

        // Récupérer les réservations du trajet
        $bookings = Booking::where('trip_id', $id)
            ->with('user:id,first_name,last_name,phone,profile_photo')
            ->get()
            ->map(fn($b) => [
                'id'      => $b->id,
                'status'  => $b->status,
                'seats'   => $b->seats ?? $b->passengers ?? 1,
                'amount'  => (float) $b->amount,
                'client'  => $b->user ? [
                    'id'    => $b->user->id,
                    'name'  => trim(($b->user->first_name ?? '') . ' ' . ($b->user->last_name ?? '')),
                    'phone' => $b->user->phone ?? '',
                ] : null,
                'created_at' => $b->created_at,
            ]);

        return response()->json([
            'success'  => true,
            'data'     => $this->formatTrip($trip),
            'bookings' => $bookings,
        ]);
    }

    /**
     * POST /admin/trips (réservation client)
     * FIX: utilise auth user() + vérifie available_seats
     */
    public function book(Request $request)
    {
        $request->validate([
            'trip_id'       => 'required|exists:trips,id',
            'seats'         => 'required|integer|min:1',
            'luggage_count' => 'nullable|integer|min:0',
            'amount'        => 'required|numeric|min:0',
        ]);

        $trip = Trip::find($request->trip_id);

        if (!$trip || $trip->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Ce trajet n\'est plus disponible.',
            ], 422);
        }

        if ($trip->available_seats < $request->seats) {
            return response()->json([
                'success' => false,
                'message' => 'Seulement '.$trip->available_seats.' place(s) disponible(s).',
            ], 422);
        }

        $user = Auth::user();

        $booking = Booking::create([
            'trip_id'        => $trip->id,
            'user_id'        => $user->id,
            'driver_id'      => $trip->driver_id,
            'seats'          => $request->seats,
            'passengers'     => $request->seats,
            'luggage_count'  => $request->luggage_count ?? 0,
            'amount'         => $request->amount,
            'status'         => 'pending',
            'payment_status' => 'pending',
        ]);

        // Ne PAS décrémenter ici — le chauffeur confirme d'abord
        // $trip->decrement('available_seats', $request->seats);

        return response()->json([
            'success' => true,
            'message' => 'Réservation créée. En attente de confirmation du chauffeur.',
            'data'    => [
                'id'         => $booking->id,
                'trip_id'    => $booking->trip_id,
                'status'     => $booking->status,
                'seats'      => $booking->seats,
                'amount'     => (float) $booking->amount,
                'created_at' => $booking->created_at,
            ],
        ], 201);
    }

    // ── Formatage uniforme ──────────────────────────────────────────────────
    private function formatTrip(Trip $trip): array
    {
        $driver = $trip->driver;

        $driverName  = $driver
            ? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''))
            : '';
        $driverPhoto = null;
        if ($driver?->profile_photo) {
            $driverPhoto = str_starts_with($driver->profile_photo, 'http')
                ? $driver->profile_photo
                : config('app.url') . '/storage/' . $driver->profile_photo;
        }

        // FIX: prix — chercher dans les deux colonnes
        $price = (float) ($trip->price_per_seat > 0
            ? $trip->price_per_seat
            : ($trip->amount ?? 0));

        // FIX: heure normalisée HH:mm
        $time = $trip->departure_time ?? '';
        if (strlen($time) > 5) $time = substr($time, 0, 5);

        // FIX: date — retourner null si vide plutôt que chaîne vide
        $date = $trip->departure_date ?? null;
        if ($date === '0000-00-00' || $date === '') $date = null;

        return [
            'id'              => $trip->id,
            'pickup_address'  => $trip->pickup_address  ?? $trip->departure   ?? '',
            'departure'       => $trip->departure        ?? $trip->pickup_address ?? '',
            'dropoff_address' => $trip->dropoff_address ?? $trip->destination  ?? '',
            'destination'     => $trip->destination      ?? $trip->dropoff_address ?? '',
            'departure_date'  => $date,
            'departure_time'  => $time,
            'price_per_seat'  => $price,
            'amount'          => $price,
            'available_seats' => (int)   ($trip->available_seats  ?? 0),
            'bookings_count'  => (int)   ($trip->bookings_count   ?? 0),
            'luggage_included'  => (int)   ($trip->luggage_included  ?? 1),
            'luggage_kg'        => (int)   ($trip->luggage_kg        ?? 1),
            'luggage_weight_kg' => (float) ($trip->luggage_weight_kg ?? 20),
            'extra_luggage_fee' => (float) ($trip->extra_luggage_fee ?? 0),
            'extra_luggage_slots'=> (int)  ($trip->extra_luggage_slots ?? 0),
            'vehicle_type'    => $trip->vehicle_type ?? '',
            'commission_rate' => 0.15,
            'status'          => $trip->status ?? 'pending',
            'distance_km'     => $trip->distance_km ?? null,
            'driver' => $driver ? [
                'id'            => $driver->id,
                'name'          => $driverName,
                'first_name'    => $driver->first_name  ?? '',
                'last_name'     => $driver->last_name   ?? '',
                'phone'         => $driver->phone       ?? '',
                'profile_photo' => $driverPhoto,
                'is_verified'   => (bool) ($driver->is_verified ?? false),
                'vehicle_brand' => $driver->vehicle_brand ?? '',
                'vehicle_model' => $driver->vehicle_model ?? '',
                'vehicle_color' => $driver->vehicle_color ?? '',
                'vehicle_plate' => $driver->vehicle_plate ?? '',
                'vehicle_type'  => $driver->vehicle_type  ?? '',
            ] : null,
            'vehicle' => $driver ? [
                'brand' => $driver->vehicle_brand ?? '',
                'model' => $driver->vehicle_model ?? '',
                'color' => $driver->vehicle_color ?? '',
                'plate' => $driver->vehicle_plate ?? '',
                'type'  => $trip->vehicle_type    ?? '',
            ] : null,
            'created_at' => $trip->created_at,
            'updated_at' => $trip->updated_at,
        ];
    }
}