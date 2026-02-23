<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_expiry',
        'license_image',
        'id_card_number',
        'id_card_image',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_year',
        'vehicle_color',
        'vehicle_plate_number',
        'vehicle_registration_image',
        'vehicle_insurance_image',
        'vehicle_type', // standard, comfort, premium
        'seats_available',
        'is_online',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'kyc_status', // pending, approved, rejected
        'kyc_verified_at',
        'kyc_rejected_reason',
        'is_available',
        'total_rides',
        'total_earnings',
        'rating_average',
        'rating_count',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'vehicle_year' => 'integer',
        'seats_available' => 'integer',
        'is_online' => 'boolean',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'last_location_update' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'is_available' => 'boolean',
        'total_rides' => 'integer',
        'total_earnings' => 'decimal:2',
        'rating_average' => 'decimal:2',
        'rating_count' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | KYC Status Constants
    |--------------------------------------------------------------------------
    */

    const KYC_PENDING = 'pending';
    const KYC_APPROVED = 'approved';
    const KYC_REJECTED = 'rejected';

    /*
    |--------------------------------------------------------------------------
    | Vehicle Type Constants
    |--------------------------------------------------------------------------
    */

    const VEHICLE_STANDARD = 'standard';
    const VEHICLE_COMFORT = 'comfort';
    const VEHICLE_PREMIUM = 'premium';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('is_online', true);
    }

    public function scopeKycApproved($query)
    {
        return $query->where('kyc_status', self::KYC_APPROVED);
    }

    public function scopeNearby($query, float $latitude, float $longitude, float $radiusKm = 10)
    {
        // Haversine formula for distance calculation
        return $query->selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * cos(radians(current_latitude)) *
                cos(radians(current_longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(current_latitude))
            )) AS distance
        ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isKycApproved(): bool
    {
        return $this->kyc_status === self::KYC_APPROVED;
    }

    public function isKycPending(): bool
    {
        return $this->kyc_status === self::KYC_PENDING;
    }

    public function isKycRejected(): bool
    {
        return $this->kyc_status === self::KYC_REJECTED;
    }

    public function canAcceptRides(): bool
    {
        return $this->isKycApproved() && $this->is_online && $this->is_available;
    }

    public function goOnline(): bool
    {
        if (!$this->isKycApproved()) {
            return false;
        }

        $this->update([
            'is_online' => true,
            'is_available' => true,
        ]);

        return true;
    }

    public function goOffline(): void
    {
        $this->update([
            'is_online' => false,
            'is_available' => false,
        ]);
    }

    public function updateLocation(float $latitude, float $longitude): void
    {
        $this->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'last_location_update' => now(),
        ]);
    }

    public function setAvailable(bool $available): void
    {
        $this->update(['is_available' => $available]);
    }

    public function incrementStats(float $earnings): void
    {
        $this->increment('total_rides');
        $this->increment('total_earnings', $earnings);
    }

    public function updateRating(float $newRating): void
    {
        $totalRating = ($this->rating_average * $this->rating_count) + $newRating;
        $newCount = $this->rating_count + 1;

        $this->update([
            'rating_average' => $totalRating / $newCount,
            'rating_count' => $newCount,
        ]);
    }

    /**
     * Get vehicle full name
     */
    public function getVehicleName(): string
    {
        return "{$this->vehicle_brand} {$this->vehicle_model} ({$this->vehicle_year})";
    }
}
