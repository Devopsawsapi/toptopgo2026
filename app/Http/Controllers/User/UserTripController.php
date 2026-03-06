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
     *   - pickup     : ville de départ (recherche partielle)
     *   - dropoff    : ville de destination (recherche partielle)
     *   - date       : date au format YYYY-MM-DD (obligatoire si fourni)
     *   - time       : heure au format HH:mm (optionnel, filtre ±1h)
     *   - passengers : nombre de places requises (défaut 1)
     */
    public function index(Request $request)
    {
        $query = Trip::with(['driver:id,first_name,last_name,phone,rating,rating_count,photo'])
            ->where('status', 'active')
            ->where('available_seats', '>=', 1);

        // ── Filtre lieu de départ ────────────────────────────────
        if ($request->filled('pickup')) {
            $query->where(function ($q) use ($request) {
                $q->where('pickup_address', 'like', '%' . $request->pickup . '%')
                  ->orWhere('departure_city', 'like', '%' . $request->pickup . '%');
            });
        }

        // ── Filtre destination ───────────────────────────────────
        if ($request->filled('dropoff')) {
            $query->where(function ($q) use ($request) {
                $q->where('dropoff_address', 'like', '%' . $request->dropoff . '%')
                  ->orWhere('destination_city', 'like', '%' . $request->dropoff . '%');
            });
        }

        // ── Filtre date ──────────────────────────────────────────
        // Si aucune date fournie → on prend aujourd'hui par défaut
        $date = $request->filled('date')
            ? $request->date
            : now()->toDateString();

        $query->whereDate('departure_date', $date);

        // ── Filtre heure (optionnel, ±1h autour de l'heure demandée) ──
        if ($request->filled('time')) {
            try {
                $requestedTime = \Carbon\Carbon::createFromFormat('H:i', $request->time);
                $from = $requestedTime->copy()->subHour()->format('H:i:s');
                $to   = $requestedTime->copy()->addHour()->format('H:i:s');
                $query->whereBetween('departure_time', [$from, $to]);
            } catch (\Exception $e) {
                // Heure invalide → on ignore le filtre heure
            }
        }

        // ── Filtre nombre de passagers ───────────────────────────
        $passengers = max(1, (int) $request->get('passengers', 1));
        $query->where('available_seats', '>=', $passengers);

        // ── Tri : prochains départs en premier ───────────────────
        $query->orderBy('departure_date', 'asc')
              ->orderBy('departure_time', 'asc');

        $trips = $query->get()->map(function ($trip) {
            return [
                'id'               => $trip->id,
                'pickup_address'   => $trip->pickup_address ?? $trip->departure_city,
                'dropoff_address'  => $trip->dropoff_address ?? $trip->destination_city,
                'departure_date'   => $trip->departure_date,
                'departure_time'   => $trip->departure_time
                    ? \Carbon\Carbon::parse($trip->departure_time)->format('H:i')
                    : null,
                'price_per_seat'   => $trip->price_per_seat ?? $trip->amount,
                'available_seats'  => $trip->available_seats,
                'vehicle_type'     => $trip->vehicle_type ?? null,
                'distance_km'      => $trip->distance_km ?? null,
                'status'           => $trip->status,
                'driver'           => $trip->driver ? [
                    'id'           => $trip->driver->id,
                    'name'         => trim($trip->driver->first_name . ' ' . $trip->driver->last_name),
                    'first_name'   => $trip->driver->first_name,
                    'last_name'    => $trip->driver->last_name,
                    'rating'       => $trip->driver->rating ?? 0,
                    'rating_count' => $trip->driver->rating_count ?? 0,
                    'photo'        => $trip->driver->photo
                        ? asset('storage/' . $trip->driver->photo)
                        : null,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'date'    => $date,
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

        return response()->json([
            'success' => true,
            'data'    => $trip,
        ]);
    }
}
