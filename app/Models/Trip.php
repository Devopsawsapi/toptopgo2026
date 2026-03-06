<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Driver\Driver;

class Trip extends Model
{
    use HasFactory;

    // ✅ Tous les champs — sans $fillable complet, Trip::create() ignore silencieusement
    // les champs non listés → prix=0, date vide, etc.
    protected $fillable = [
        'driver_id',
        'user_id',
        // ── Itinéraire ──
        'departure',
        'pickup_address',
        'pickup_point',
        'departure_city',
        'pickup_lat',
        'pickup_lng',
        'destination',
        'dropoff_address',
        'dropoff_point',
        'destination_city',
        'dropoff_lat',
        'dropoff_lng',
        // ── Date & heure ──
        'departure_date',
        'departure_time',
        // ── Tarification ──
        'price_per_seat',
        'amount',
        'commission',
        'driver_net',
        // ── Places ──
        'available_seats',
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

    // ── Relations ─────────────────────────────────────────────────────────

    /**
     * Chauffeur lié au trajet
     * Namespace réel : App\Models\Driver\Driver (sous-dossier)
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class)->withDefault([
            'first_name'    => 'Chauffeur',
            'last_name'     => '',
            'phone'         => '',
            'rating'        => 0,
            'profile_photo' => null,
        ]);
    }

    /**
     * Alias "vehicle" attendu par Admin\TripController
     * Les infos véhicule sont sur le Driver dans ce projet
     */
    public function vehicle()
    {
        return $this->belongsTo(Driver::class, 'driver_id')->withDefault([
            'vehicle_type'  => '',
            'vehicle_brand' => '',
            'vehicle_model' => '',
            'vehicle_color' => '',
            'vehicle_plate' => '',
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ── Accesseur utile ───────────────────────────────────────────────────

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
