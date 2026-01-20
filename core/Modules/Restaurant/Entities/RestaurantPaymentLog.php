<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RestaurantPaymentLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\RestaurantPaymentLogFactory::new();
    }

    public function menu_order(){
        return $this->belongsTo(MenuOrder::class,'menu_order_id','id');
    }
}
