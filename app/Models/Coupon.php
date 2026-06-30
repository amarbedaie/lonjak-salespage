<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'code', 'type', 'value', 'active', 'usage_limit', 'used_count', 'expires_at'])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        if (! $this->active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /** Discount amount (RM) for a given subtotal, capped at the subtotal. */
    public function discountFor(float $subtotal): float
    {
        $d = $this->type === 'fixed'
            ? (float) $this->value
            : $subtotal * (float) $this->value / 100;

        return round(min($d, $subtotal), 2);
    }
}
