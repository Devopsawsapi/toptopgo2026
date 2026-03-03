<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Trip;
use App\Http\Resources\MessageResource;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class DriverMessageController extends Controller
{
    public function index(Request $request)
    {
        $driver = $request->user();

        $trips = Trip::where('driver_id', $driver->id)
                     ->with(['messages' => function ($q) {
                         $q->latest()->limit(1);
                     }])
                     ->latest()
                     ->get();

        return response()->json(['success' => true, 'data' => $trips]);
    }

    public function show(Request $request, $tripId)
    {
        $driver = $request->user();

        $trip = Trip::where('id', $tripId)
                    ->where('driver_id', $driver->id)
                    ->firstOrFail();

        $messages = Message::where('trip_id', $tripId)->oldest()->get();

        // Marquer comme lus
        Message::where('trip_id', $tripId)
               ->where('receiver_id', $driver->id)
               ->where('receiver_type', get_class($driver))
               ->where('is_read', false)
               ->update(['is_read' => true, 'read_at' => now()]);

        return MessageResource::collection($messages);
    }

    public function store(Request $request, $tripId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $driver = $request->user();

        $trip = Trip::where('id', $tripId)
                    ->where('driver_id', $driver->id)
                    ->firstOrFail();

        $message = Message::create([
            'trip_id'       => $tripId,
            'sender_type'   => get_class($driver),
            'sender_id'     => $driver->id,
            'receiver_type' => \App\Models\User\User::class,
            'receiver_id'   => $trip->user_id,
            'content'       => $request->content,
        ]);

        MessageSent::dispatch($message);

        return new MessageResource($message);
    }
}