<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'ride_id',
        'type', // ride_payment, driver_credit, withdrawal, refund, top_up
        'provider', // peex, mtn_momo, airtel_money, stripe, internal
        'provider_transaction_id',
        'amount',
        'commission',
        'driver_amount',
        'currency',
        'status', // pending, processing, completed, failed, cancelled, refunded, escrowed
        'provider_response',
        'metadata',
        'completed_at',
        'failed_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'driver_amount' => 'decimal:2',
        'provider_response' => 'array',
        'metadata' => 'array',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Type Constants
    |--------------------------------------------------------------------------
    */

    const TYPE_RIDE_PAYMENT = 'ride_payment';
    const TYPE_DRIVER_CREDIT = 'driver_credit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_REFUND = 'refund';
    const TYPE_TOP_UP = 'top_up';

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_ESCROWED = 'escrowed';

    /*
    |--------------------------------------------------------------------------
    | Provider Constants
    |--------------------------------------------------------------------------
    */

    const PROVIDER_PEEX = 'peex';
    const PROVIDER_MTN_MOMO = 'mtn_momo';
    const PROVIDER_AIRTEL_MONEY = 'airtel_money';
    const PROVIDER_STRIPE = 'stripe';
    const PROVIDER_INTERNAL = 'internal';

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeRidePayments($query)
    {
        return $query->where('type', self::TYPE_RIDE_PAYMENT);
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAWAL);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isEscrowed(): bool
    {
        return $this->status === self::STATUS_ESCROWED;
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_ESCROWED]);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Get provider display name
     */
    public function getProviderName(): string
    {
        return match ($this->provider) {
            self::PROVIDER_PEEX => 'Peex',
            self::PROVIDER_MTN_MOMO => 'MTN Mobile Money',
            self::PROVIDER_AIRTEL_MONEY => 'Airtel Money',
            self::PROVIDER_STRIPE => 'Stripe (Carte)',
            self::PROVIDER_INTERNAL => 'Interne',
            default => $this->provider,
        };
    }

    /**
     * Get type display name
     */
    public function getTypeName(): string
    {
        return match ($this->type) {
            self::TYPE_RIDE_PAYMENT => 'Paiement trajet',
            self::TYPE_DRIVER_CREDIT => 'Crédit chauffeur',
            self::TYPE_WITHDRAWAL => 'Retrait',
            self::TYPE_REFUND => 'Remboursement',
            self::TYPE_TOP_UP => 'Rechargement',
            default => $this->type,
        };
    }

    /**
     * Get status display name
     */
    public function getStatusName(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_PROCESSING => 'En cours',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_FAILED => 'Échoué',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_REFUNDED => 'Remboursé',
            self::STATUS_ESCROWED => 'Séquestré',
            default => $this->status,
        };
    }
}
