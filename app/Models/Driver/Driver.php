<?php

namespace App\Models\Driver;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Role;
use App\Models\Vehicle;
use App\Models\DriverDocument;
use App\Models\Wallet;
use App\Models\Trip;
use App\Models\DriverLocation;
use App\Models\SupportMessage;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'birth_date', 'birth_place', 'country_birth',
        'phone', 'profile_photo', 'id_card_front', 'id_card_back',
        'license_front', 'license_back', 'vehicle_registration', 'insurance',
        'id_card_issue_date', 'id_card_expiry_date', 'id_card_issue_city', 'id_card_issue_country',
        'license_issue_date', 'license_expiry_date', 'license_issue_city', 'license_issue_country',
        'vehicle_plate', 'vehicle_brand', 'vehicle_model', 'vehicle_type', 'vehicle_color',
        'vehicle_country', 'vehicle_city', 'vehicle_lat', 'vehicle_lng', 'status', 'driver_status',
        'password',
    ];

    protected $hidden = ['password'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function documents()
    {
        return $this->hasMany(DriverDocument::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function locations()
    {
        return $this->hasMany(DriverLocation::class);
    }

    public function latestLocation()
    {
        return $this->hasOne(DriverLocation::class)->latestOfMany();
    }

    public function supportMessages()
    {
        return $this->morphMany(SupportMessage::class, 'recipient');
    }
}