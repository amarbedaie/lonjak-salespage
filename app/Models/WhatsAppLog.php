<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'type', 'to', 'session', 'status', 'waha_message_id', 'error'])]
class WhatsAppLog extends Model
{
    protected $table = 'whatsapp_logs';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
