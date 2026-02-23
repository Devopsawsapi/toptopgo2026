<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role', // passenger, driver, admin
        'avatar',
        'date_of_birth',
        'gender',
        'country_code',
        'is_phone_verified',
        'is_email_verified',
        'is_active',
        'fcm_token',
        'stripe_account_id',
        'preferred_payment_method',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_phone_verified' => 'boolean',
        'is_email_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_name'];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver');
    }

    public function scopePassengers($query)
    {
        return $query->where('role', 'passenger');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function ridesAsPassenger()
    {
        return $this->hasMany(Ride::class, 'passenger_id');
    }

    public function ridesAsDriver()
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'rater_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function isPassenger(): bool
    {
        return $this->role === 'passenger';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getAverageRating(): float
    {
        return $this->ratings()->avg('rating') ?? 0.0;
    }

    public function getTotalRides(): int
    {
        if ($this->isDriver()) {
            return $this->ridesAsDriver()->where('status', 'completed')->count();
        }

        return $this->ridesAsPassenger()->where('status', 'completed')->count();
    }

    public function getWalletBalance(): float
    {
        return $this->wallet?->balance ?? 0;
    }

    /**
     * Send FCM notification
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}
