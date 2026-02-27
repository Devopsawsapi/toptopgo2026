<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRate extends Model
{
    protected $fillable = [
        'country_id',
        'city_id',
        'rate',
        'is_active',
        'note',
    ];

    protected $casts = [
        'rate'      => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Résoudre le taux applicable pour un couple (country_id, city_id).
     * Priorité : ville > pays > null (retourne null si aucun taux trouvé)
     */
    public static function resolveRate(?int $countryId, ?int $cityId): ?self
    {
        // 1. Taux spécifique à la ville
        if ($cityId) {
            $rate = self::where('city_id', $cityId)
                        ->where('is_active', true)
                        ->first();
            if ($rate) return $rate;
        }

        // 2. Taux par pays (sans ville)
        if ($countryId) {
            $rate = self::where('country_id', $countryId)
                        ->whereNull('city_id')
                        ->where('is_active', true)
                        ->first();
            if ($rate) return $rate;
        }

        return null;
    }
}