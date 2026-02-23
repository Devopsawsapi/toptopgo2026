<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $driver,
        public float $latitude,
        public float $longitude,
        public ?int $rideId = null
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        // If driver has an active ride, broadcast to passenger
        if ($this->rideId) {
            $ride = $this->driver->ridesAsDriver()->find($this->rideId);
            if ($ride && $ride->isActive()) {
                $channels[] = new PrivateChannel('ride.' . $this->rideId);
                $channels[] = new PrivateChannel('user.' . $ride->passenger_id);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'driver.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->driver->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ride_id' => $this->rideId,
            'updated_at' => now()->toISOString(),
        ];
    }
}
