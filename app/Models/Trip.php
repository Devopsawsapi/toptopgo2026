<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Driver\Driver;
use App\Models\User\User;

class Trip extends Model
{
    protected $fillable = [
        'driver_id', 'user_id', 'pickup_address', 'pickup_lat', 'pickup_lng',
        'dropoff_address', 'dropoff_lat', 'dropoff_lng', 'vehicle_type',
        'distance_km', 'amount', 'commission', 'driver_net',
        'status', 'started_at', 'completed_at',
    ];

    // Relation vers le chauffeur
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Relation vers le client (anciennement user)
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Paiement lié au trajet
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Réservation liée au trajet
    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    // Messages liés au trajet
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Appels liés au trajet
    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    // Alertes SOS liées au trajet
    public function sosAlerts()
    {
        return $this->hasMany(SosAlert::class);
    }
}