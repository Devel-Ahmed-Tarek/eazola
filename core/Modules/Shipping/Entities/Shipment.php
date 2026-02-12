<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $table = 'shipments';

    protected $fillable = [
        'order_id',
        'provider',
        'external_shipment_id',
        'tracking_number',
        'carrier_name',
        'service_type',
        'status',
        'label_url',
        'tracking_url',
        'shipping_cost',
        'currency',
        'meta',
    ];

    protected $casts = [
        'shipping_cost' => 'float',
        'meta' => 'array',
    ];
}

