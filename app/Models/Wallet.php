<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithPositiveBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Credit funds to wallet
     */
    public function credit(float $amount, string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);

        return true;
    }

    /**
     * Debit funds from wallet
     */
    public function debit(float $amount, string $description = null): bool
    {
        if ($amount <= 0 || $this->balance < $amount) {
            return false;
        }

        $this->decrement('balance', $amount);

        return true;
    }

    /**
     * Hold funds for pending withdrawal
     */
    public function holdForWithdrawal(float $amount): bool
    {
        if ($amount <= 0 || $this->balance < $amount) {
            return false;
        }

        $this->decrement('balance', $amount);
        $this->increment('pending_balance', $amount);

        return true;
    }

    /**
     * Release held funds after successful withdrawal
     */
    public function releaseHeldFunds(float $amount): bool
    {
        if ($amount <= 0 || $this->pending_balance < $amount) {
            return false;
        }

        $this->decrement('pending_balance', $amount);
        $this->increment('total_withdrawn', $amount);

        return true;
    }

    /**
     * Return held funds to balance (on failed withdrawal)
     */
    public function returnHeldFunds(float $amount): bool
    {
        if ($amount <= 0 || $this->pending_balance < $amount) {
            return false;
        }

        $this->decrement('pending_balance', $amount);
        $this->increment('balance', $amount);

        return true;
    }

    /**
     * Get available balance (excluding pending)
     */
    public function getAvailableBalance(): float
    {
        return $this->balance;
    }

    /**
     * Get total balance (including pending)
     */
    public function getTotalBalance(): float
    {
        return $this->balance + $this->pending_balance;
    }

    /**
     * Check if withdrawal is possible
     */
    public function canWithdraw(float $amount): bool
    {
        $minimumWithdrawal = config('payments.minimum_withdrawal', 1000);

        return $this->is_active
            && $amount >= $minimumWithdrawal
            && $this->balance >= $amount;
    }

    /**
     * Get formatted balance with currency
     */
    public function getFormattedBalance(): string
    {
        return number_format($this->balance, 0, ',', ' ') . ' ' . $this->currency;
    }
}
