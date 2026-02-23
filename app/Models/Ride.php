<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id',
        'driver_id',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'distance_km',
        'duration_minutes',
        'price',
        'price_per_km',
        'currency',
        'status', // pending, accepted, driver_arriving, in_progress, completed, cancelled
        'payment_status', // pending, escrowed, completed, refunded, failed
        'payment_method',
        'scheduled_at',
        'accepted_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'payment_released_at',
        'notes',
        'vehicle_type',
        'seats_requested',
    ];

    protected $casts = [
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'dropoff_latitude' => 'decimal:8',
        'dropoff_longitude' => 'decimal:8',
        'distance_km' => 'decimal:2',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'seats_requested' => 'integer',
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_released_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DRIVER_ARRIVING = 'driver_arriving';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_ESCROWED = 'escrowed';
    const PAYMENT_COMPLETED = 'completed';
    const PAYMENT_REFUNDED = 'refunded';
    const PAYMENT_FAILED = 'failed';

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ACCEPTED,
            self::STATUS_DRIVER_ARRIVING,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeForPassenger($query, int $passengerId)
    {
        return $query->where('passenger_id', $passengerId);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACCEPTED,
            self::STATUS_DRIVER_ARRIVING,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_DRIVER_ARRIVING,
        ]);
    }

    public function isPaymentCompleted(): bool
    {
        return $this->payment_status === self::PAYMENT_COMPLETED;
    }

    public function accept(User $driver): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->update([
            'driver_id' => $driver->id,
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        return true;
    }

    public function start(): bool
    {
        if ($this->status !== self::STATUS_DRIVER_ARRIVING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return true;
    }

    public function complete(): bool
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return true;
    }

    public function cancel(int $cancelledBy, string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Calculate estimated price based on distance
     */
    public static function calculatePrice(float $distanceKm, string $vehicleType = 'standard'): float
    {
        $baseFare = config('rides.base_fare', 500); // 500 XAF
        $pricePerKm = config("rides.price_per_km.{$vehicleType}", 150); // 150 XAF/km
        $minimumFare = config('rides.minimum_fare', 1000); // 1000 XAF

        $price = $baseFare + ($distanceKm * $pricePerKm);

        return max($price, $minimumFare);
    }
}
