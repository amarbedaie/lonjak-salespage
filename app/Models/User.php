<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'business_name', 'phone', 'email', 'password', 'role', 'plan', 'ai_credits', 'status', 'bayarcash_pat', 'bayarcash_portal_key', 'bayarcash_api_secret', 'bayarcash_sandbox', 'bayarcash_active'])]
#[Hidden(['password', 'remember_token', 'bayarcash_pat', 'bayarcash_api_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ai_credits' => 'integer',
            'bayarcash_pat' => 'encrypted',
            'bayarcash_api_secret' => 'encrypted',
            'bayarcash_sandbox' => 'boolean',
            'bayarcash_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Merchant has online payment (BayarCash) configured & enabled. */
    public function hasBayarcash(): bool
    {
        return $this->bayarcash_active && $this->bayarcash_pat && $this->bayarcash_api_secret && $this->bayarcash_portal_key;
    }

    public function isSuspended(): bool
    {
        return $this->status === 'digantung';
    }

    public function salespages(): HasMany
    {
        return $this->hasMany(Salespage::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
