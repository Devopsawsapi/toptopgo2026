<?php
namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * CORRECTIONS :
 * 1. store(): departure_time → HH:mm:ss seulement (VARCHAR 10/20)
 * 2. store(): pickup_address + departure synchronisés
 * 3. start(): vérifie driver_id (fix "No query results for Trip")
 * 4. start(): exige status = accepted
 * 5. confirmBooking(): flow pending → confirmed → trip accepted
 */
class TripController extends Controller
{
    public function index(Request $request)
    {
        $driver = Auth::guard('driver')->user();
        $trips = Trip::where('driver_id', $driver->id)
            ->with(['bookings.user', 'vehicle'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($t) => $this->formatTrip($t));
        return response()->json(['success' => true, 'data' => $trips]);
    }

    public function store(Request $request)
    {
        $driver = Auth::guard('driver')->user();

        $request->validate([
            'departure'        => 'required_without:pickup_address|string|max:255',
            'pickup_address'   => 'required_without:departure|string|max:255',
            'destination'      => 'required_without:dropoff_address|string|max:255',
            'dropoff_address'  => 'required_without:destination|string|max:255',
            'price_per_seat'   => 'required|numeric|min:0',
            'available_seats'  => 'required|integer|min:1|max:20',
            'departure_date'   => 'required|date',
            'departure_time'   => 'required|string',
            'luggage_included' => 'nullable|integer|min:0',
            'extra_luggage_fee'=> 'nullable|numeric|min:0',
            'vehicle_type'     => 'nullable|string',
        ]);

        // FIX PRINCIPAL: extraire seulement l'heure depuis n'importe quel format
        $timeRaw = $request->input('departure_time', '00:00:00');
        if (strlen($timeRaw) > 8) {
            // "2026-03-06 09:00:00" → "09:00:00"
            try { $timeRaw = Carbon::parse($timeRaw)->format('H:i:s'); }
            catch (\Exception $e) { $timeRaw = substr($timeRaw, 11, 8); }
        }
        if (strlen($timeRaw) === 5) $timeRaw .= ':00'; // "09:00" → "09:00:00"

        $departure   = $request->input('departure')   ?? $request->input('pickup_address');
        $destination = $request->input('destination') ?? $request->input('dropoff_address');

        $trip = Trip::create([
            'driver_id'         => $driver->id,
            'pickup_address'    => $departure,
            'departure'         => $departure,
            'dropoff_address'   => $destination,
            'destination'       => $destination,
            'pickup_lat'        => $request->input('pickup_lat', 0),
            'pickup_lng'        => $request->input('pickup_lng', 0),
            'dropoff_lat'       => $request->input('dropoff_lat', 0),
            'dropoff_lng'       => $request->input('dropoff_lng', 0),
            'price_per_seat'    => $request->input('price_per_seat'),
            'amount'            => $request->input('price_per_seat'),
            'available_seats'   => $request->input('available_seats', 3),
            'departure_date'    => $request->input('departure_date'),
            'departure_time'    => $timeRaw,            // HH:mm:ss max
            'luggage_included'  => $request->input('luggage_included', 1),
            'luggage_kg'        => $request->input('luggage_included', 1),
            'extra_luggage_fee' => $request->input('extra_luggage_fee', 0),
            'vehicle_type'      => $request->input('vehicle_type', 'Confort'),
            'status'            => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trajet publié avec succès',
            'data'    => $this->formatTrip($trip->fresh(['bookings', 'vehicle'])),
        ], 201);
    }

    public function start(Request $request, $id)
    {
        $driver = Auth::guard('driver')->user();

        // FIX: toujours filtrer par driver_id → évite "No query results for Trip"
        $trip = Trip::where('id', $id)->where('driver_id', $driver->id)->first();

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'Trajet introuvable ou accès non autorisé',
            ], 404);
        }

        // FIX: seulement les trajets 'accepted' peuvent démarrer
        if ($trip->status !== 'accepted') {
            $msg = match($trip->status) {
                'pending'     => 'En attente de réservations confirmées. Confirmez d\'abord les réservations clients.',
                'in_progress' => 'Ce trajet est déjà en cours.',
                'completed'   => 'Ce trajet est déjà terminé.',
                'cancelled'   => 'Ce trajet a été annulé.',
                default       => 'Statut invalide : ' . $trip->status,
            };
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        $trip->update(['status' => 'in_progress', 'started_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Trajet démarré !',
            'data'    => $this->formatTrip($trip->fresh()),
        ]);
    }

    public function end(Request $request, $id)
    {
        $driver = Auth::guard('driver')->user();
        $trip = Trip::where('id', $id)->where('driver_id', $driver->id)
                    ->where('status', 'in_progress')->first();

        if (!$trip) {
            return response()->json(['success' => false, 'message' => 'Trajet non trouvé ou non en cours'], 404);
        }

        $trip->update(['status' => 'completed', 'completed_at' => now()]);
        Booking::where('trip_id', $trip->id)
            ->whereIn('status', ['accepted', 'confirmed', 'paid'])
            ->update(['status' => 'completed']);

        return response()->json(['success' => true, 'message' => 'Trajet terminé !',
            'data' => $this->formatTrip($trip->fresh())]);
    }

    public function confirmBooking(Request $request, $bookingId)
    {
        $driver  = Auth::guard('driver')->user();
        $booking = Booking::whereHas('trip', fn($q) => $q->where('driver_id', $driver->id))
                          ->where('id', $bookingId)->where('status', 'pending')->first();

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Réservation introuvable'], 404);
        }

        $booking->update(['status' => 'confirmed']);

        // Si plus aucune réservation pending → le trip passe à accepted (peut démarrer)
        $trip = $booking->trip;
        $hasPending = $trip->bookings()->where('status', 'pending')->exists();
        if (!$hasPending && $trip->bookings()->whereIn('status', ['confirmed', 'paid'])->exists()) {
            $trip->update(['status' => 'accepted']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée ! Le client peut maintenant payer.',
            'data'    => $booking->fresh(['user', 'trip']),
        ]);
    }

    public function rejectBooking(Request $request, $bookingId)
    {
        $driver  = Auth::guard('driver')->user();
        $booking = Booking::whereHas('trip', fn($q) => $q->where('driver_id', $driver->id))
                          ->where('id', $bookingId)->whereIn('status', ['pending', 'confirmed'])->first();

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Réservation introuvable'], 404);
        }

        $booking->update(['status' => 'rejected']);
        return response()->json(['success' => true, 'message' => 'Réservation rejetée.']);
    }

    private function formatTrip(Trip $trip): array
    {
        $dep = $trip->departure      ?? $trip->pickup_address  ?? '';
        $dst = $trip->destination    ?? $trip->dropoff_address ?? '';
        return [
            'id'                => $trip->id,
            'pickup_address'    => $dep,
            'departure'         => $dep,
            'dropoff_address'   => $dst,
            'destination'       => $dst,
            'departure_date'    => $trip->departure_date,
            'departure_time'    => $trip->departure_time,
            'price_per_seat'    => $trip->price_per_seat ?? $trip->amount,
            'amount'            => $trip->amount         ?? $trip->price_per_seat,
            'available_seats'   => $trip->available_seats,
            'luggage_included'  => $trip->luggage_included ?? $trip->luggage_kg ?? 1,
            'luggage_kg'        => $trip->luggage_kg      ?? $trip->luggage_included ?? 1,
            'extra_luggage_fee' => $trip->extra_luggage_fee ?? 0,
            'vehicle_type'      => $trip->vehicle_type,
            'status'            => $trip->status,
            'started_at'        => $trip->started_at,
            'completed_at'      => $trip->completed_at,
            'created_at'        => $trip->created_at,
            'driver'            => $trip->driver ? [
                'id'            => $trip->driver->id,
                'first_name'    => $trip->driver->first_name,
                'last_name'     => $trip->driver->last_name,
                'name'          => trim("{$trip->driver->first_name} {$trip->driver->last_name}"),
                'phone'         => $trip->driver->phone,
                'profile_photo' => $trip->driver->profile_photo,
            ] : null,
            'vehicle'           => [
                'brand' => $trip->vehicle?->brand ?? $trip->driver?->vehicle_brand,
                'model' => $trip->vehicle?->model ?? $trip->driver?->vehicle_model,
                'plate' => $trip->vehicle?->plate ?? $trip->driver?->vehicle_plate,
                'color' => $trip->vehicle?->color ?? $trip->driver?->vehicle_color,
            ],
            'bookings'          => $trip->bookings ? $trip->bookings->map(fn($b) => [
                'id'     => $b->id,
                'status' => $b->status,
                'seats'  => $b->seats ?? $b->passengers ?? 1,
                'amount' => $b->amount,
                'user'   => $b->user ? [
                    'id'    => $b->user->id,
                    'name'  => trim("{$b->user->first_name} {$b->user->last_name}"),
                    'phone' => $b->user->phone,
                ] : null,
            ])->toArray() : [],
        ];
    }
}
