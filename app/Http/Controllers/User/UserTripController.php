<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class UserTripController extends Controller
{
    /**
     * GET /user/trips
     * Liste des trajets disponibles pour le client
     */
    public function index(Request $request)
    {
        $query = Trip::with(['driver', 'driver.vehicle'])
            ->where('status', 'pending')
            ->where('available_seats', '>', 0);

        // Filtres optionnels
        if ($request->pickup) {
            $query->where(function ($q) use ($request) {
                $q->where('departure', 'like', '%' . $request->pickup . '%')
                  ->orWhere('pickup_address', 'like', '%' . $request->pickup . '%');
            });
        }
        if ($request->dropoff) {
            $query->where(function ($q) use ($request) {
                $q->where('destination', 'like', '%' . $request->dropoff . '%')
                  ->orWhere('dropoff_address', 'like', '%' . $request->dropoff . '%');
            });
        }
        if ($request->date) {
            $query->where('departure_date', $request->date);
        }

        $trips = $query->orderBy('departure_date')->orderBy('departure_time')->get();

        return response()->json([
            'success' => true,
            'data'    => $trips->map(fn($t) => $this->format($t)),
        ]);
    }

    /**
     * GET /user/trips/{id}
     * Détail d'un trajet
     */
    public function show($id)
    {
        $trip = Trip::with(['driver', 'driver.vehicle'])->find($id);

        if (!$trip) {
            return response()->json(['success' => false, 'message' => 'Trajet introuvable'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->format($trip),
        ]);
    }

    /**
     * Formater un trajet avec tous les champs attendus par Flutter
     */
    private function format(Trip $trip): array
    {
        $driver  = $trip->driver;
        $vehicle = $driver?->vehicle ?? null;

        // Prix — chercher dans les deux colonnes possibles
        $price = (float) ($trip->price_per_seat ?? $trip->amount ?? 0);

        // Heure normalisée HH:mm
        $time = $trip->departure_time ?? '';
        if (strlen($time) > 5) $time = substr($time, 0, 5);

        // Photo chauffeur URL complète
        $driverPhoto = null;
        if ($driver?->profile_photo) {
            $driverPhoto = str_starts_with($driver->profile_photo, 'http')
                ? $driver->profile_photo
                : asset('storage/' . $driver->profile_photo);
        }

        // Nom chauffeur
        $driverName = trim(($driver?->first_name ?? '') . ' ' . ($driver?->last_name ?? ''));

        return [
            'id'              => $trip->id,

            // Itinéraire — double alias pour compatibilité Flutter
            'pickup_address'  => $trip->pickup_address  ?? $trip->departure   ?? '',
            'departure'       => $trip->departure        ?? $trip->pickup_address ?? '',
            'dropoff_address' => $trip->dropoff_address ?? $trip->destination  ?? '',
            'destination'     => $trip->destination      ?? $trip->dropoff_address ?? '',

            // Date & heure
            'departure_date'  => $trip->departure_date ?? '',
            'departure_time'  => $time,

            // Prix dans les deux champs
            'price_per_seat'  => $price,
            'amount'          => $price,

            // Places & bagages
            'available_seats'   => (int)   ($trip->available_seats   ?? 0),
            'luggage_included'  => (int)   ($trip->luggage_included  ?? $trip->luggage_kg ?? 1),
            'luggage_kg'        => (int)   ($trip->luggage_kg        ?? $trip->luggage_included ?? 1),
            'luggage_weight_kg' => (float) ($trip->luggage_weight_kg ?? 20),
            'extra_luggage_fee' => (float) ($trip->extra_luggage_fee ?? 0),

            // Véhicule
            'vehicle_type'    => $trip->vehicle_type ?? '',
            'vehicle'         => $vehicle ? [
                'brand' => $vehicle->brand ?? $vehicle->make ?? $driver?->vehicle_brand ?? '',
                'model' => $vehicle->model ?? $driver?->vehicle_model ?? '',
                'color' => $vehicle->color ?? $driver?->vehicle_color ?? '',
                'plate' => $vehicle->plate ?? $vehicle->license_plate ?? $driver?->vehicle_plate ?? '',
                'year'  => $vehicle->year  ?? $driver?->vehicle_year  ?? '',
                'type'  => $vehicle->type  ?? $trip->vehicle_type ?? '',
            ] : [
                // Fallback sur les colonnes directes du driver
                'brand' => $driver?->vehicle_brand ?? '',
                'model' => $driver?->vehicle_model ?? '',
                'color' => $driver?->vehicle_color ?? '',
                'plate' => $driver?->vehicle_plate ?? '',
                'year'  => $driver?->vehicle_year  ?? '',
                'type'  => $trip->vehicle_type      ?? '',
            ],

            // Commission
            'commission_rate' => 0.15,

            'status'      => $trip->status ?? 'pending',
            'distance_km' => $trip->distance_km ?? null,

            // Chauffeur complet
            'driver' => $driver ? [
                'id'            => $driver->id,
                'name'          => $driverName,
                'first_name'    => $driver->first_name ?? '',
                'last_name'     => $driver->last_name  ?? '',
                'phone'         => $driver->phone      ?? '',
                'profile_photo' => $driverPhoto,
                'is_verified'   => (bool) ($driver->is_verified ?? false),
                'vehicle_brand' => $driver->vehicle_brand ?? $vehicle?->brand ?? '',
                'vehicle_model' => $driver->vehicle_model ?? $vehicle?->model ?? '',
                'vehicle_color' => $driver->vehicle_color ?? $vehicle?->color ?? '',
                'vehicle_plate' => $driver->vehicle_plate ?? $vehicle?->plate ?? '',
            ] : null,

            'created_at' => $trip->created_at,
        ];
    }
}