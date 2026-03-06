<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class UserTripController extends Controller
{
    /**
     * GET /api/user/trips
     * Paramètres acceptés :
     *   departure   : ville départ (aussi pickup=)
     *   destination : ville arrivée (aussi dropoff=)
     *   date        : YYYY-MM-DD (optionnel, défaut = aujourd'hui et futur)
     *   time        : HH:mm (optionnel, filtre ±1h)
     *   passengers  : int (défaut 1)
     */
    public function index(Request $request)
    {
        $query = Trip::with(['driver:id,first_name,last_name,phone,rating,rating_count,profile_photo'])
            // ✅ FIX PRINCIPAL : 'active' n'existait pas dans l'enum
            // Les chauffeurs créent avec status='pending'
            ->whereIn('status', ['pending', 'accepted'])
            ->where('available_seats', '>=', 1);

        // ── Filtre départ (supporte departure= et pickup=) ──────────────
        $dep = $request->filled('departure') ? $request->departure
             : ($request->filled('pickup')   ? $request->pickup : null);

        if ($dep) {
            $query->where(function ($q) use ($dep) {
                $q->where('pickup_address',   'like', "%$dep%")
                  ->orWhere('departure',      'like', "%$dep%")
                  ->orWhere('departure_city', 'like', "%$dep%");
            });
        }

        // ── Filtre destination (supporte destination= et dropoff=) ──────
        $dest = $request->filled('destination') ? $request->destination
              : ($request->filled('dropoff')    ? $request->dropoff : null);

        if ($dest) {
            $query->where(function ($q) use ($dest) {
                $q->where('dropoff_address',   'like', "%$dest%")
                  ->orWhere('destination',     'like', "%$dest%")
                  ->orWhere('destination_city','like', "%$dest%");
            });
        }

        // ── Filtre date ──────────────────────────────────────────────────
        if ($request->filled('date')) {
            $query->whereDate('departure_date', $request->date);
        } else {
            // Sans date → trajets d'aujourd'hui et futurs + ceux sans date
            $query->where(function ($q) {
                $q->whereNull('departure_date')
                  ->orWhereDate('departure_date', '>=', now()->toDateString());
            });
        }

        // ── Filtre heure ±1h (optionnel) ─────────────────────────────────
        if ($request->filled('time')) {
            try {
                $t    = \Carbon\Carbon::createFromFormat('H:i', $request->time);
                $from = $t->copy()->subHour()->format('H:i:s');
                $to   = $t->copy()->addHour()->format('H:i:s');
                $query->where(function ($q) use ($from, $to) {
                    $q->whereNull('departure_time')
                      ->orWhereBetween('departure_time', [$from, $to]);
                });
            } catch (\Exception $e) {}
        }

        // ── Filtre passagers ─────────────────────────────────────────────
        $passengers = max(1, (int) $request->get('passengers', 1));
        $query->where('available_seats', '>=', $passengers);

        // ── Tri ──────────────────────────────────────────────────────────
        $query->orderBy('departure_date', 'asc')
              ->orderBy('departure_time', 'asc');

        $trips = $query->get()->map(fn($trip) => $this->fmt($trip));

        return response()->json([
            'success' => true,
            'count'   => $trips->count(),
            'data'    => $trips,
        ]);
    }

    /**
     * GET /api/user/trips/{id}
     */
    public function show($id)
    {
        $trip = Trip::with(['driver:id,first_name,last_name,phone,rating,profile_photo'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->fmt($trip),
        ]);
    }

    // ── Formatage uniforme ────────────────────────────────────────────────
    private function fmt(Trip $trip): array
    {
        $price = (float) ($trip->price_per_seat ?? $trip->amount ?? 0);
        $time  = $trip->departure_time ?? null;
        if ($time && strlen($time) > 5) $time = substr($time, 0, 5);

        $driver = $trip->driver;
        $photo  = null;
        if ($driver && $driver->profile_photo) {
            $photo = str_starts_with($driver->profile_photo, 'http')
                ? $driver->profile_photo
                : asset('storage/' . $driver->profile_photo);
        }

        return [
            'id'                  => $trip->id,
            'pickup_address'      => $trip->pickup_address   ?? $trip->departure      ?? '',
            'dropoff_address'     => $trip->dropoff_address  ?? $trip->destination    ?? '',
            'departure'           => $trip->departure        ?? $trip->pickup_address ?? '',
            'destination'         => $trip->destination      ?? $trip->dropoff_address ?? '',
            'pickup_point'        => $trip->pickup_point     ?? null,
            'dropoff_point'       => $trip->dropoff_point    ?? null,
            'departure_date'      => $trip->departure_date
                ? $trip->departure_date->format('Y-m-d')
                : null,
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
            'driver'              => $driver ? [
                'id'           => $driver->id,
                'name'         => trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')),
                'first_name'   => $driver->first_name  ?? '',
                'last_name'    => $driver->last_name   ?? '',
                'rating'       => (float) ($driver->rating       ?? 0),
                'rating_count' => (int)   ($driver->rating_count ?? 0),
                'photo'        => $photo,
            ] : null,
        ];
    }
}
