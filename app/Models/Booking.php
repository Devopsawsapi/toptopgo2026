<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Booking extends Model
{
    protected $fillable = ['user_id', 'trip_id', 'status', 'booked_at'];

    public function user() { return $this->belongsTo(User::class); }
    public function trip() { return $this->belongsTo(Trip::class); }
}
