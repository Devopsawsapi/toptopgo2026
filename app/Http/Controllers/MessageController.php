<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Trip;
use App\Http\Resources\MessageResource;
use App\Events\MessageSent;

class MessageController extends Controller
{
    public function index(Request $request, $tripId)
    {
        // Vérifier que l'utilisateur a accès à ce trip
        $user = $request->user();
        $trip = Trip::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('driver_id', $user->id);
        })->findOrFail($tripId);

        $messages = Message::where('trip_id', $tripId)
            ->oldest()
            ->get();

        // Marquer comme lus
        Message::where('trip_id', $tripId)
            ->where('receiver_id', $user->id)
            ->where('receiver_type', get_class($user))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return MessageResource::collection($messages);
    }

    public function send(Request $request, $tripId)
    {
        $request->validate([
            'content'       => 'required|string|max:1000',
            'receiver_id'   => 'required|integer',
            'receiver_type' => 'required|in:user,driver',
        ]);

        $sender = $request->user();

        $receiverClass = match($request->receiver_type) {
            'user'   => \App\Models\User\User::class,
            'driver' => \App\Models\Driver\Driver::class,
        };

        $message = Message::create([
            'trip_id'       => $tripId,
            'sender_type'   => get_class($sender),
            'sender_id'     => $sender->id,
            'receiver_type' => $receiverClass,
            'receiver_id'   => $request->receiver_id,
            'content'       => $request->content,
        ]);

        // Broadcaster via WebSocket
        MessageSent::dispatch($message);

        return new MessageResource($message);
    }
}
