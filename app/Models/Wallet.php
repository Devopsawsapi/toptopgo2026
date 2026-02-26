<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Driver\Driver;

class Wallet extends Model
{
    protected $fillable = ['driver_id', 'balance', 'currency'];

    public function driver() { return $this->belongsTo(Driver::class); }
    public function transactions() { return $this->hasMany(WalletTransaction::class); }
    public function withdrawals() { return $this->hasMany(Withdrawal::class); }
}
