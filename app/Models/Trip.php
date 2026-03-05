<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Driver\Driver;
use App\Models\User\User;

class Trip extends Model
{
    protected $fillable = [
        'driver_id', 'user_id',
        'pickup_address', 'departure_city',
        'pickup_lat', 'pickup_lng',
        'dropoff_address', 'destination_city',
        'dropoff_lat', 'dropoff_lng',
        'vehicle_type', 'distance_km',
        'amount', 'commission', 'driver_net',
        'available_seats', 'luggage_kg',
        'departure_time',
        'status', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'amount'         => 'float',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function sosAlerts()
    {
        return $this->hasMany(SosAlert::class);
    }
}