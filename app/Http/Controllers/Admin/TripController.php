<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    /**
     * GET /client/trips  — Liste des trajets disponibles
     * FIX: retourne price_per_seat, departure_time, available_seats, vehicle, driver complet
     */
    public function index(Request $request)
    {
        $query = Trip::with([
            'driver.vehicle',   // FIX: charger le véhicule via le driver
            'driver.user',
        ])
        ->where('status', 'pending')         // uniquement les trajets ouverts
        ->where('available_seats', '>', 0);  // avec des places disponibles

        // Filtres optionnels
        if ($request->pickup) {
            $query->where(function($q) use ($request) {
                $q->where('pickup_address', 'like', '%'.$request->pickup.'%')
                  ->orWhere('departure',    'like', '%'.$request->pickup.'%');
            });
        }
        if ($request->dropoff) {
            $query->where(function($q) use ($request) {
                $q->where('dropoff_address', 'like', '%'.$request->dropoff.'%')
                  ->orWhere('destination',   'like', '%'.$request->dropoff.'%');
            });
        }
        if ($request->date) {
            $query->where('departure_date', $request->date);
        }

        $trips = $query->orderBy('departure_date')->orderBy('departure_time')->get();

        return response()->json([
            'success' => true,
            'data'    => $trips->map(fn($t) => $this->formatTrip($t)),
        ]);
    }

    /**
     * GET /client/trips/{id}  — Détail d'un trajet
     */
    public function show($id)
    {
        $trip = Trip::with(['driver.vehicle', 'driver.user'])->find($id);

        if (!$trip) {
            return response()->json(['success' => false, 'message' => 'Trajet introuvable'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatTrip($trip),
        ]);
    }

    /**
     * POST /client/bookings  — Réserver un trajet
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
                'message' => 'Nombre de places insuffisant. Seulement '.$trip->available_seats.' place(s) disponible(s).',
            ], 422);
        }

        $user = Auth::user();

        $booking = Booking::create([
            'trip_id'         => $trip->id,
            'user_id'         => $user->id,
            'driver_id'       => $trip->driver_id,
            'seats'           => $request->seats,
            'passengers'      => $request->seats,
            'luggage_count'   => $request->luggage_count ?? 0,
            'amount'          => $request->amount,
            'status'          => 'pending',
            'payment_status'  => 'pending',
        ]);

        // Décrémenter les places disponibles
        $trip->decrement('available_seats', $request->seats);

        return response()->json([
            'success' => true,
            'message' => 'Réservation créée avec succès.',
            'data'    => $booking,
        ]);
    }

    /**
     * FIX: Formatage uniforme d'un trajet pour le client
     * Retourne TOUS les champs nécessaires à l'app Flutter
     */
    private function formatTrip(Trip $trip): array
    {
        $driver  = $trip->driver;
        $vehicle = $driver?->vehicle ?? null;
        $user    = $driver?->user ?? null;

        // FIX: prix — chercher dans les deux colonnes possibles
        $pricePerSeat = $trip->price_per_seat ?? $trip->amount ?? 0;

        // FIX: heure — normaliser au format HH:mm
        $depTime = $trip->departure_time ?? '';
        if (strlen($depTime) > 5) {
            $depTime = substr($depTime, 0, 5); // "09:00:00" → "09:00"
        }

        // Nom chauffeur
        $driverName = '';
        if ($user) {
            $driverName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            if (empty($driverName)) $driverName = $user->name ?? '';
        } elseif ($driver) {
            $driverName = $driver->name ?? $driver->first_name ?? '';
        }

        // Photo de profil
        $driverPhoto = null;
        if ($driver && $driver->profile_photo) {
            $photo = $driver->profile_photo;
            $driverPhoto = str_starts_with($photo, 'http')
                ? $photo
                : config('app.url') . '/storage/' . $photo;
        }

        return [
            'id'               => $trip->id,

            // FIX: double alias pour compatibilité Flutter
            'pickup_address'   => $trip->pickup_address ?? $trip->departure ?? '',
            'departure'        => $trip->departure ?? $trip->pickup_address ?? '',
            'dropoff_address'  => $trip->dropoff_address ?? $trip->destination ?? '',
            'destination'      => $trip->destination ?? $trip->dropoff_address ?? '',

            // FIX: date et heure séparées et propres
            'departure_date'   => $trip->departure_date ?? '',
            'departure_time'   => $depTime,

            // FIX: prix TOUJOURS retourné dans les deux champs
            'price_per_seat'   => (float) $pricePerSeat,
            'amount'           => (float) $pricePerSeat,

            // Places
            'available_seats'  => (int) ($trip->available_seats ?? 0),

            // Bagages
            'luggage_included'    => (int) ($trip->luggage_included ?? $trip->luggage_kg ?? 1),
            'luggage_kg'          => (int) ($trip->luggage_kg ?? $trip->luggage_included ?? 1),
            'luggage_weight_kg'   => (float) ($trip->luggage_weight_kg ?? 20),
            'extra_luggage_fee'   => (float) ($trip->extra_luggage_fee ?? 0),

            // Véhicule
            'vehicle_type'     => $trip->vehicle_type ?? '',

            // Commission TopTopGo (15%)
            'commission_rate'  => 0.15,

            'status'           => $trip->status ?? 'pending',
            'distance_km'      => $trip->distance_km ?? null,

            // FIX: objet driver complet avec photo
            'driver' => $driver ? [
                'id'            => $driver->id,
                'name'          => $driverName,
                'first_name'    => $user?->first_name ?? $driver->first_name ?? '',
                'last_name'     => $user?->last_name  ?? $driver->last_name  ?? '',
                'phone'         => $user?->phone ?? $driver->phone ?? '',
                'profile_photo' => $driverPhoto,
                'is_verified'   => (bool) ($driver->is_verified ?? false),
                // Infos véhicule dans le driver aussi (fallback)
                'vehicle_brand' => $vehicle?->brand ?? $vehicle?->make ?? '',
                'vehicle_model' => $vehicle?->model ?? '',
                'vehicle_color' => $vehicle?->color ?? '',
                'vehicle_plate' => $vehicle?->plate ?? $vehicle?->license_plate ?? '',
            ] : null,

            // FIX: objet vehicle complet
            'vehicle' => $vehicle ? [
                'id'            => $vehicle->id,
                'brand'         => $vehicle->brand ?? $vehicle->make ?? '',
                'model'         => $vehicle->model ?? '',
                'color'         => $vehicle->color ?? '',
                'plate'         => $vehicle->plate ?? $vehicle->license_plate ?? '',
                'year'          => $vehicle->year ?? '',
                'type'          => $vehicle->type ?? $trip->vehicle_type ?? '',
            ] : null,

            'created_at' => $trip->created_at,
            'updated_at' => $trip->updated_at,
        ];
    }
}
