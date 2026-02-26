<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\BookTripRequest;
use App\Http\Resources\TripResource;
use App\Services\TripService;
use App\Events\TripCreated;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function book(Request $request)
    {
        $request->validate([
            'pickup_address' => 'required|string',
            'pickup_lat'     => 'required|numeric',
            'pickup_lng'     => 'required|numeric',
            'dropoff_address'=> 'required|string',
            'dropoff_lat'    => 'required|numeric',
            'dropoff_lng'    => 'required|numeric',
            'vehicle_type'   => 'required|in:Standard,Confort,Van,PMR',
            'amount'         => 'required|numeric|min:0',
            'method'         => 'required|in:mtn,orange,airtel,moov,visa,mastercard',
        ]);

        $commission = $request->amount * 0.20;
        $driverNet  = $request->amount - $commission;

        $trip = Trip::create([
            'user_id'         => $request->user()->id,
            'driver_id'       => 1, // assigné plus tard
            'pickup_address'  => $request->pickup_address,
            'pickup_lat'      => $request->pickup_lat,
            'pickup_lng'      => $request->pickup_lng,
            'dropoff_address' => $request->dropoff_address,
            'dropoff_lat'     => $request->dropoff_lat,
            'dropoff_lng'     => $request->dropoff_lng,
            'vehicle_type'    => $request->vehicle_type,
            'amount'          => $request->amount,
            'commission'      => $commission,
            'driver_net'      => $driverNet,
            'status'          => 'pending',
        ]);

        Booking::create([
            'user_id'   => $request->user()->id,
            'trip_id'   => $trip->id,
            'status'    => 'confirmed',
            'booked_at' => now(),
        ]);

        Payment::create([
            'user_id'    => $request->user()->id,
            'trip_id'    => $trip->id,
            'driver_id'  => 1,
            'amount'     => $request->amount,
            'commission' => $commission,
            'driver_net' => $driverNet,
            'method'     => $request->method,
            'status'     => 'pending',
            'country'    => $request->user()->country,
            'city'       => $request->user()->city,
        ]);

        return response()->json(['message' => 'Course créée.', 'trip' => $trip], 201);
    }

    public function myTrips(Request $request)
    {
        return response()->json(
            Trip::with('driver')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(20)
        );
    }

    public function cancel(Request $request, $id)
    {
        $trip = Trip::where('id', $id)->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->firstOrFail();
        $trip->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Course annulée.']);
    }
}
