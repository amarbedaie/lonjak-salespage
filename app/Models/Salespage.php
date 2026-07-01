<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'slug', 'product_name', 'price', 'compare_price', 'category', 'theme', 'status', 'gateway', 'brief', 'blocks', 'variants', 'variant_index', 'images', 'video_url', 'visits', 'fb_pixel', 'tiktok_pixel', 'ga_id', 'offer_ends_at', 'bump_enabled', 'bump_title', 'bump_desc', 'bump_price'])]
class Salespage extends Model
{
    protected function casts(): array
    {
        return [
            'brief' => 'array',
            'blocks' => 'array',
            'variants' => 'array',
            'images' => 'array',
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'offer_ends_at' => 'datetime',
            'bump_enabled' => 'boolean',
            'bump_price' => 'decimal:2',
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
