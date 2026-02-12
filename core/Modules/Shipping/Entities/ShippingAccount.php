<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;

class ShippingAccount extends Model
{
    protected $table = 'shipping_accounts';

    protected $fillable = [
        'provider',
        'api_key',
        'base_url',
        'enabled',
        'meta',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'meta' => 'array',
    ];
}

