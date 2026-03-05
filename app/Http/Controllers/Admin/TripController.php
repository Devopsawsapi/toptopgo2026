<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['driver'])  // plus de driver.vehicle
            ->where('status', 'pending')
            ->where('available_seats', '>', 0);

        if ($request->pickup) {
            $query->where(function($q) use ($request) {
                $q->where('pickup_address', 'like', '%'.$request->pickup.'%')
                  ->orWhere('departure', 'like', '%'.$request->pickup.'%');
            });
        }

        if ($request->dropoff) {
            $query->where(function($q) use ($request) {
                $q->where('dropoff_address', 'like', '%'.$request->dropoff.'%')
                  ->orWhere('destination', 'like', '%'.$request->dropoff.'%');
            });
        }

        if ($request->date) {
            $query->where('departure_date', $request->date);
        }

        $trips = $query->orderBy('departure_date')->orderBy('departure_time')->get();

        return response()->json([
            'success' => true,
            'data' => $trips->map(fn($t) => $this->formatTrip($t)),
        ]);
    }

    public function show($id)
    {
        $trip = Trip::with(['driver'])->find($id);

        if (!$trip) {
            return response()->json(['success' => false, 'message' => 'Trajet introuvable'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatTrip($trip),
        ]);
    }

    public function book(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seats' => 'required|integer|min:1',
            'luggage_count' => 'nullable|integer|min:0',
            'amount' => 'required|numeric|min:0',
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
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'driver_id' => $trip->driver_id,
            'seats' => $request->seats,
            'passengers' => $request->seats,
            'luggage_count' => $request->luggage_count ?? 0,
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $trip->decrement('available_seats', $request->seats);

        return response()->json([
            'success' => true,
            'message' => 'Réservation créée avec succès.',
            'data' => $booking,
        ]);
    }

    private function formatTrip(Trip $trip): array
    {
        $driver = $trip->driver;

        $driverName = $driver ? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')) : '';
        $driverPhoto = $driver?->profile_photo 
            ? (str_starts_with($driver->profile_photo, 'http') 
                ? $driver->profile_photo 
                : config('app.url') . '/storage/' . $driver->profile_photo)
            : null;

        return [
            'id' => $trip->id,
            'pickup_address' => $trip->pickup_address ?? $trip->departure ?? '',
            'departure' => $trip->departure ?? $trip->pickup_address ?? '',
            'dropoff_address' => $trip->dropoff_address ?? $trip->destination ?? '',
            'destination' => $trip->destination ?? $trip->dropoff_address ?? '',
            'departure_date' => $trip->departure_date ?? '',
            'departure_time' => substr($trip->departure_time ?? '', 0, 5),
            'price_per_seat' => (float) ($trip->price_per_seat ?? $trip->amount ?? 0),
            'amount' => (float) ($trip->price_per_seat ?? $trip->amount ?? 0),
            'available_seats' => (int) ($trip->available_seats ?? 0),
            'luggage_included' => (int) ($trip->luggage_included ?? $trip->luggage_kg ?? 1),
            'luggage_kg' => (int) ($trip->luggage_kg ?? $trip->luggage_included ?? 1),
            'luggage_weight_kg' => (float) ($trip->luggage_weight_kg ?? 20),
            'extra_luggage_fee' => (float) ($trip->extra_luggage_fee ?? 0),
            'vehicle_type' => $trip->vehicle_type ?? '',
            'commission_rate' => 0.15,
            'status' => $trip->status ?? 'pending',
            'distance_km' => $trip->distance_km ?? null,
            'driver' => $driver ? [
                'id' => $driver->id,
                'name' => $driverName,
                'first_name' => $driver->first_name ?? '',
                'last_name' => $driver->last_name ?? '',
                'phone' => $driver->phone ?? '',
                'profile_photo' => $driverPhoto,
                'is_verified' => (bool) ($driver->is_verified ?? false),
                'vehicle_brand' => $driver->vehicle_brand ?? '',
                'vehicle_model' => $driver->vehicle_model ?? '',
                'vehicle_color' => $driver->vehicle_color ?? '',
                'vehicle_plate' => $driver->vehicle_plate ?? '',
            ] : null,
            'vehicle' => $driver ? [
                'brand' => $driver->vehicle_brand ?? '',
                'model' => $driver->vehicle_model ?? '',
                'color' => $driver->vehicle_color ?? '',
                'plate' => $driver->vehicle_plate ?? '',
                'type' => $trip->vehicle_type ?? '',
            ] : null,
            'created_at' => $trip->created_at,
            'updated_at' => $trip->updated_at,
        ];
    }
}