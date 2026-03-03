<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use App\Services\TripService;
use App\Events\TripStatusUpdated;
use App\Notifications\TripAcceptedNotification;
use App\Notifications\TripCompletedNotification;
use App\Models\Trip;
use Illuminate\Http\Request;

class DriverTripController extends Controller
{
    public function __construct(private TripService $tripService) {}

    public function index(Request $request)
    {
        $trips = Trip::with('user', 'payment')
                     ->where('driver_id', $request->user()->id)
                     ->latest()
                     ->paginate(20);
        return TripResource::collection($trips);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pickup_address'    => 'required|string',
            'dropoff_address'   => 'required|string',
            'pickup_latitude'   => 'required|numeric',
            'pickup_longitude'  => 'required|numeric',
            'dropoff_latitude'  => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
        ]);

        $trip = Trip::create([
            'driver_id'         => $request->user()->id,
            'pickup_address'    => $request->pickup_address,
            'dropoff_address'   => $request->dropoff_address,
            'pickup_latitude'   => $request->pickup_latitude,
            'pickup_longitude'  => $request->pickup_longitude,
            'dropoff_latitude'  => $request->dropoff_latitude,
            'dropoff_longitude' => $request->dropoff_longitude,
            'status'            => 'pending',
        ]);

        return response()->json([
            'message' => 'Trajet créé avec succès.',
            'trip'    => new TripResource($trip),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $trip = Trip::where('id', $id)
                    ->where('driver_id', $request->user()->id)
                    ->firstOrFail();
        return new TripResource($trip);
    }

    public function available(Request $request)
    {
        $driver = $request->user();
        if ($driver->status !== 'approved') {
            return response()->json(['message' => 'Compte non approuvé.'], 403);
        }
        $trips = Trip::with('user')->where('status', 'pending')->latest()->get();
        return TripResource::collection($trips);
    }

    public function accept(Request $request, $id)
    {
        $driver = $request->user();
        if ($driver->status !== 'approved') {
            return response()->json(['message' => 'Compte non approuvé.'], 403);
        }
        $trip = Trip::where('id', $id)->where('status', 'pending')->firstOrFail();
        $trip->update(['driver_id' => $driver->id, 'status' => 'accepted']);
        $trip->user?->notify(new TripAcceptedNotification($trip->load('driver')));
        TripStatusUpdated::dispatch($trip);
        return response()->json([
            'message' => 'Course acceptée.',
            'trip'    => new TripResource($trip->load('user')),
        ]);
    }

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

    public function end(Request $request, $id)
    {
        $trip = Trip::where('id', $id)
                    ->where('driver_id', $request->user()->id)
                    ->where('status', 'in_progress')
                    ->firstOrFail();
        $this->tripService->completeTrip($trip);
        $trip->user?->notify(new TripCompletedNotification($trip));
        $request->user()->notify(new TripCompletedNotification($trip));
        TripStatusUpdated::dispatch($trip);
        return response()->json([
            'message' => 'Course terminée.',
            'trip'    => new TripResource($trip),
        ]);
    }
}