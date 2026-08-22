<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row = one attempt to grant a customer access on a BeDaie system
 * after their Mendap order was paid. Unique per (order, system).
 */
#[Fillable(['order_id', 'system', 'idempotency_key', 'status', 'external_user_id', 'magic_link', 'login_url', 'response', 'error', 'attempts'])]
class ProvisioningCall extends Model
{
    protected function casts(): array
    {
        return [
            'response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
