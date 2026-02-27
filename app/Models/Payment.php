<?php

//app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'trip_id', 'driver_id',
        'amount', 'commission', 'driver_net',
        'method', 'status', 'transaction_ref',
        'country', 'city', 'paid_at'
    ];

    protected $dates = ['paid_at'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver\Driver::class, 'driver_id');
    }

    public function trip()
    {
        return $this->belongsTo(\App\Models\Trip::class);
    }
}