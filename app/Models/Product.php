<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'sku', 'price', 'compare_price', 'cost', 'stock', 'sold', 'image', 'status', 'category', 'description', 'audience', 'problem', 'benefits', 'tone', 'images', 'video_url'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'images' => 'array',
        ];
    }

    /** First stored image as a public URL, or null. */
    public function thumbnailUrl(): ?string
    {
        $first = is_array($this->images) ? ($this->images[0] ?? null) : null;

        return $first ? asset('storage/'.$first) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
