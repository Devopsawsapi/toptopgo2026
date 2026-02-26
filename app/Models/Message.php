<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'trip_id', 'sender_type', 'sender_id',
        'receiver_type', 'receiver_id',
        'content', 'is_read', 'read_at',
    ];

    public function trip() { return $this->belongsTo(Trip::class); }
    public function sender() { return $this->morphTo(); }
    public function receiver() { return $this->morphTo(); }
}
