<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'salespage_id', 'customer', 'email', 'phone', 'address', 'state', 'product_name', 'qty', 'total', 'status', 'courier', 'awb', 'payment_status', 'payment_ref'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salespage(): BelongsTo
    {
        return $this->belongsTo(Salespage::class);
    }
}
