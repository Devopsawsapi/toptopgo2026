<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'trip_id'       => $this->trip_id,
            'content'       => $this->content,
            'is_read'       => $this->is_read,
            'read_at'       => $this->read_at?->toDateTimeString(),
            'created_at'    => $this->created_at?->toDateTimeString(),
            'sender_type'   => class_basename($this->sender_type),
            'sender_id'     => $this->sender_id,
            'receiver_type' => class_basename($this->receiver_type),
            'receiver_id'   => $this->receiver_id,
        ];
    }
}
