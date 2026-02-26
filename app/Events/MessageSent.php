<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('trip.' . $this->message->trip_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'            => $this->message->id,
            'trip_id'       => $this->message->trip_id,
            'content'       => $this->message->content,
            'sender_type'   => class_basename($this->message->sender_type),
            'sender_id'     => $this->message->sender_id,
            'created_at'    => $this->message->created_at?->toDateTimeString(),
        ];
    }
}
