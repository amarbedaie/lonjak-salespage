<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfraSalespage extends Model
{
    protected $fillable = ['command_uuid', 'produk', 'salespage', 'utm', 'status'];

    protected $casts = ['produk' => 'array', 'salespage' => 'array', 'utm' => 'array'];
}
