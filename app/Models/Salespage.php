<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'slug', 'product_name', 'price', 'compare_price', 'category', 'status', 'gateway', 'brief', 'blocks', 'images', 'video_url', 'visits', 'fb_pixel', 'tiktok_pixel', 'ga_id', 'offer_ends_at'])]
class Salespage extends Model
{
    protected function casts(): array
    {
        return [
            'brief' => 'array',
            'blocks' => 'array',
            'images' => 'array',
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'offer_ends_at' => 'datetime',
        ];
    }

    /** Stored image paths as public URLs. */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])->map(fn ($p) => asset('storage/'.$p))->all();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
