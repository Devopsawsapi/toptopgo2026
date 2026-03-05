<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Trip;
use App\Models\User\User;
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
            }, 'user'])
            ->latest()
            ->get();

        $data = $trips->map(function ($trip) {

            $user = $trip->user;

            $lastMessage = $trip->messages->first();

            $clientPhoto = null;

            if ($user && $user->profile_photo) {
                $clientPhoto = str_starts_with($user->profile_photo, 'http')
                    ? $user->profile_photo
                    : asset('storage/' . $user->profile_photo);
            }

            $unread = Message::where('trip_id', $trip->id)
                ->where('sender_type', User::class)
                ->where('is_read', false)
                ->count();

            return [
                'trip_id' => $trip->id,
                'client_id' => $user?->id,
                'client_name' => $user
                    ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    : 'Client',

                'client_photo' => $clientPhoto,
                'client_phone' => $user?->phone ?? '',
                'trip_status' => $trip->status ?? 'pending',

                'last_message' => $lastMessage?->content ?? '',
                'updated_at' => $lastMessage?->created_at ?? $trip->updated_at,

                'unread_count' => $unread
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function show(Request $request, $tripId)
    {
        $driver = $request->user();

        $trip = Trip::where('id', $tripId)
            ->where('driver_id', $driver->id)
            ->firstOrFail();

        $messages = Message::where('trip_id', $tripId)->oldest()->get();

        Message::where('trip_id', $tripId)
            ->where('receiver_id', $driver->id)
            ->where('receiver_type', get_class($driver))
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

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
            'trip_id' => $tripId,
            'sender_type' => get_class($driver),
            'sender_id' => $driver->id,
            'receiver_type' => User::class,
            'receiver_id' => $trip->user_id,
            'content' => $request->content,
        ]);

        MessageSent::dispatch($message);

        return new MessageResource($message);
    }
}