<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class UserTripController extends Controller
{
    /**
     * GET /api/user/trips
     * Retourne les trajets disponibles avec filtres :
     *   - departure  : ville de départ (recherche partielle)
     *   - destination: ville de destination (recherche partielle)
     *   - pickup     : alias départ
     *   - dropoff    : alias destination
     *   - date       : date au format YYYY-MM-DD
     *   - time       : heure au format HH:mm (optionnel, ±1h)
     *   - passengers : nombre de places requises (défaut 1)
     */
    public function index(Request $request)
    {
        $query = Trip::with(['driver:id,first_name,last_name,phone,rating,rating_count,photo'])
            // ✅ FIX PRINCIPAL : 'active' n'existe PAS dans l'enum de la migration
            // Les chauffeurs créent des trajets avec status='pending'
            // On affiche pending + accepted (trajet confirmé mais pas encore démarré)
            ->whereIn('status', ['pending', 'accepted'])
            ->where('available_seats', '>=', 1);

        // ── Filtre lieu de départ (supporte departure= et pickup=) ──────────
        $departureQuery = $request->filled('departure')
            ? $request->departure
            : ($request->filled('pickup') ? $request->pickup : null);

        if ($departureQuery) {
            $query->where(function ($q) use ($departureQuery) {
                $q->where('pickup_address',   'like', '%' . $departureQuery . '%')
                  ->orWhere('departure',      'like', '%' . $departureQuery . '%')
                  ->orWhere('departure_city', 'like', '%' . $departureQuery . '%');
            });
        }

        // ── Filtre destination (supporte destination= et dropoff=) ───────────
        $destinationQuery = $request->filled('destination')
            ? $request->destination
            : ($request->filled('dropoff') ? $request->dropoff : null);

        if ($destinationQuery) {
            $query->where(function ($q) use ($destinationQuery) {
                $q->where('dropoff_address',   'like', '%' . $destinationQuery . '%')
                  ->orWhere('destination',      'like', '%' . $destinationQuery . '%')
                  ->orWhere('destination_city', 'like', '%' . $destinationQuery . '%');
            });
        }

        // ── Filtre date ──────────────────────────────────────────────────────
        // Si date fournie → on filtre, sinon on prend à partir d'aujourd'hui
        if ($request->filled('date')) {
            $query->whereDate('departure_date', $request->date);
        } else {
            // Trajets d'aujourd'hui et futurs (pas les trajets passés)
            $query->where(function ($q) {
                $q->whereNull('departure_date')
                  ->orWhereDate('departure_date', '>=', now()->toDateString());
            });
        }

        // ── Filtre heure (optionnel, ±1h autour de l'heure demandée) ────────
        if ($request->filled('time')) {
            try {
                $requestedTime = \Carbon\Carbon::createFromFormat('H:i', $request->time);
                $from = $requestedTime->copy()->subHour()->format('H:i:s');
                $to   = $requestedTime->copy()->addHour()->format('H:i:s');
                $query->where(function ($q) use ($from, $to) {
                    $q->whereNull('departure_time')
                      ->orWhereBetween('departure_time', [$from, $to]);
                });
            } catch (\Exception $e) {
                // Heure invalide → on ignore le filtre heure
            }
        }

        // ── Filtre nombre de passagers ────────────────────────────────────
        $passengers = max(1, (int) $request->get('passengers', 1));
        $query->where('available_seats', '>=', $passengers);

        // ── Tri : prochains départs en premier ───────────────────────────
        $query->orderBy('departure_date', 'asc')
              ->orderBy('departure_time', 'asc');

        $trips = $query->get()->map(function ($trip) {
            $price = (float) ($trip->price_per_seat ?? $trip->amount ?? 0);
            $time  = $trip->departure_time ?? null;
            if ($time && strlen($time) > 5) {
                $time = substr($time, 0, 5); // HH:mm
            }

            return [
                'id'              => $trip->id,
                'pickup_address'  => $trip->pickup_address  ?? $trip->departure      ?? '',
                'dropoff_address' => $trip->dropoff_address ?? $trip->destination    ?? '',
                'departure'       => $trip->departure       ?? $trip->pickup_address ?? '',
                'destination'     => $trip->destination     ?? $trip->dropoff_address ?? '',
                // ── Lieu précis embarquement ──
                'pickup_point'    => $trip->pickup_point    ?? null,
                'dropoff_point'   => $trip->dropoff_point   ?? null,
                // ── Date & heure ──
                'departure_date'  => $trip->departure_date  ?? null,
                'departure_time'  => $time,
                // ── Prix & places ──
                'price_per_seat'  => $price,
                'amount'          => $price,
                'available_seats' => (int) ($trip->available_seats ?? 0),
                // ── Véhicule ──
                'vehicle_type'    => $trip->vehicle_type    ?? null,
                'distance_km'     => $trip->distance_km     ?? null,
                // ── Bagages ──
                'luggage_included'    => (int)   ($trip->luggage_included    ?? 1),
                'luggage_weight_kg'   => (float) ($trip->luggage_weight_kg   ?? 20),
                'extra_luggage_fee'   => (float) ($trip->extra_luggage_fee   ?? 0),
                'extra_luggage_slots' => (int)   ($trip->extra_luggage_slots ?? 0),
                // ── Statut ──
                'status'          => $trip->status,
                // ── Chauffeur ──
                'driver'          => $trip->driver ? [
                    'id'           => $trip->driver->id,
                    'name'         => trim(($trip->driver->first_name ?? '') . ' ' . ($trip->driver->last_name ?? '')),
                    'first_name'   => $trip->driver->first_name  ?? '',
                    'last_name'    => $trip->driver->last_name   ?? '',
                    'rating'       => (float) ($trip->driver->rating       ?? 0),
                    'rating_count' => (int)   ($trip->driver->rating_count ?? 0),
                    'photo'        => $trip->driver->photo
                        ? (str_starts_with($trip->driver->photo, 'http')
                            ? $trip->driver->photo
                            : asset('storage/' . $trip->driver->photo))
                        : null,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $trips->count(),
            'data'    => $trips,
        ]);
    }

    /**
     * GET /api/user/trips/{id}
     * Détail d'un trajet
     */
    public function show($id)
    {
        $trip = Trip::with(['driver:id,first_name,last_name,phone,rating,photo'])
            ->findOrFail($id);

        $price = (float) ($trip->price_per_seat ?? $trip->amount ?? 0);
        $time  = $trip->departure_time ?? null;
        if ($time && strlen($time) > 5) $time = substr($time, 0, 5);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                  => $trip->id,
                'pickup_address'      => $trip->pickup_address   ?? $trip->departure      ?? '',
                'dropoff_address'     => $trip->dropoff_address  ?? $trip->destination    ?? '',
                'departure'           => $trip->departure        ?? $trip->pickup_address ?? '',
                'destination'         => $trip->destination      ?? $trip->dropoff_address ?? '',
                'pickup_point'        => $trip->pickup_point     ?? null,
                'dropoff_point'       => $trip->dropoff_point    ?? null,
                'departure_date'      => $trip->departure_date   ?? null,
                'departure_time'      => $time,
                'price_per_seat'      => $price,
                'amount'              => $price,
                'available_seats'     => (int)   ($trip->available_seats     ?? 0),
                'luggage_included'    => (int)   ($trip->luggage_included    ?? 1),
                'luggage_weight_kg'   => (float) ($trip->luggage_weight_kg   ?? 20),
                'extra_luggage_fee'   => (float) ($trip->extra_luggage_fee   ?? 0),
                'extra_luggage_slots' => (int)   ($trip->extra_luggage_slots ?? 0),
                'vehicle_type'        => $trip->vehicle_type ?? null,
                'distance_km'         => $trip->distance_km  ?? null,
                'status'              => $trip->status,
                'driver'              => $trip->driver ? [
                    'id'       => $trip->driver->id,
                    'name'     => trim(($trip->driver->first_name ?? '') . ' ' . ($trip->driver->last_name ?? '')),
                    'rating'   => (float) ($trip->driver->rating ?? 0),
                    'photo'    => $trip->driver->photo
                        ? (str_starts_with($trip->driver->photo, 'http')
                            ? $trip->driver->photo
                            : asset('storage/' . $trip->driver->photo))
                        : null,
                ] : null,
            ],
        ]);
    }
}
