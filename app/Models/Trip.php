<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trip extends Model
{
    use HasFactory;

    // ✅ FIX PRINCIPAL : tous les champs doivent être ici
    // Sans ça, Trip::create([...]) ignore silencieusement les champs non listés
    protected $fillable = [
        // ── Identité ──
        'driver_id',
        'user_id',

        // ── Itinéraire ──
        'departure',
        'pickup_address',
        'pickup_point',          // lieu précis embarquement
        'departure_city',
        'pickup_lat',
        'pickup_lng',

        'destination',
        'dropoff_address',
        'dropoff_point',         // lieu précis de dépose
        'destination_city',
        'dropoff_lat',
        'dropoff_lng',

        // ── Date & heure ──
        'departure_date',        // ✅ était manquant
        'departure_time',        // ✅ était manquant

        // ── Tarification ──
        'price_per_seat',        // ✅ était manquant — c'est pour ça que prix = 0
        'amount',
        'commission',
        'driver_net',

        // ── Places ──
        'available_seats',       // ✅ était manquant
        'total_seats',

        // ── Bagages ──
        'luggage_included',
        'luggage_kg',
        'luggage_weight_kg',
        'extra_luggage_fee',
        'extra_luggage_slots',

        // ── Véhicule ──
        'vehicle_type',
        'distance_km',

        // ── Statut ──
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'departure_date'      => 'date:Y-m-d',
        'price_per_seat'      => 'float',
        'amount'              => 'float',
        'commission'          => 'float',
        'driver_net'          => 'float',
        'available_seats'     => 'integer',
        'luggage_included'    => 'integer',
        'luggage_weight_kg'   => 'float',
        'extra_luggage_fee'   => 'float',
        'extra_luggage_slots' => 'integer',
        'pickup_lat'          => 'float',
        'pickup_lng'          => 'float',
        'dropoff_lat'         => 'float',
        'dropoff_lng'         => 'float',
        'started_at'          => 'datetime',
        'completed_at'        => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ── Accesseurs utiles ──────────────────────────────────────────────────
    public function getConfirmedSeatsAttribute(): int
    {
        return (int) $this->bookings()
            ->whereIn('status', ['confirmed', 'paid'])
            ->sum('seats');
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->price_per_seat ?? $this->amount ?? 0);
    }
}
