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

class TripController extends Controller
{
    public function __construct(private TripService $tripService) {}

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
        return response()->json(['message' => 'Course acceptée.', 'trip' => new TripResource($trip->load('user'))]);
    }

    public function start(Request $request, $id)
    {
        $trip = Trip::where('id', $id)->where('driver_id', $request->user()->id)->where('status', 'accepted')->firstOrFail();
        $trip->update(['status' => 'in_progress', 'started_at' => now()]);
        TripStatusUpdated::dispatch($trip);
        return response()->json(['message' => 'Course démarrée.', 'trip' => new TripResource($trip)]);
    }

    public function complete(Request $request, $id)
    {
        $trip = Trip::where('id', $id)->where('driver_id', $request->user()->id)->where('status', 'in_progress')->firstOrFail();
        $this->tripService->completeTrip($trip);
        $trip->user?->notify(new TripCompletedNotification($trip));
        $request->user()->notify(new TripCompletedNotification($trip));
        TripStatusUpdated::dispatch($trip);
        return response()->json(['message' => 'Course terminée.', 'trip' => new TripResource($trip)]);
    }

    public function myTrips(Request $request)
    {
        $trips = Trip::with('user', 'payment')->where('driver_id', $request->user()->id)->latest()->paginate(20);
        return TripResource::collection($trips);
    }
}
