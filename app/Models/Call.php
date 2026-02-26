<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'trip_id', 'caller_type', 'caller_id',
        'receiver_type', 'receiver_id',
        'type', 'status', 'duration_seconds',
        'started_at', 'ended_at',
    ];

    public function trip() { return $this->belongsTo(Trip::class); }
    public function caller() { return $this->morphTo(); }
    public function receiver() { return $this->morphTo(); }
}
