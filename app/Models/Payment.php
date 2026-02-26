<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\Driver\Driver;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'trip_id', 'driver_id', 'amount',
        'commission', 'driver_net', 'method', 'status',
        'transaction_ref', 'country', 'city', 'paid_at',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function trip() { return $this->belongsTo(Trip::class); }
}
