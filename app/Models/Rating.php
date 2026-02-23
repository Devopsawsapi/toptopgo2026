<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'rater_user_id',
        'rated_user_id',
        'rating',
        'comment',
        'type', // passenger_to_driver, driver_to_passenger
    ];

    protected $casts = [
        'rating' => 'decimal:1',
    ];

    /*
    |--------------------------------------------------------------------------
    | Type Constants
    |--------------------------------------------------------------------------
    */

    const TYPE_PASSENGER_TO_DRIVER = 'passenger_to_driver';
    const TYPE_DRIVER_TO_PASSENGER = 'driver_to_passenger';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_user_id');
    }

    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForDriver($query, int $driverId)
    {
        return $query->where('rated_user_id', $driverId)
            ->where('type', self::TYPE_PASSENGER_TO_DRIVER);
    }

    public function scopeForPassenger($query, int $passengerId)
    {
        return $query->where('rated_user_id', $passengerId)
            ->where('type', self::TYPE_DRIVER_TO_PASSENGER);
    }
}
