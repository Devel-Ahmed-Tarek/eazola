<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\User;

class MenuOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\MenuOrderFactory::new();
    }

    public function menu_billing()
    {
        return $this->hasOne(MenuBillingInfo::class, 'menu_order_id','id');
    }

    public function menu_shipping()
    {
        return $this->hasOne(MenuShippingInfo::class, 'menu_order_id','id');
    }

    public function payment_log()
    {
        return $this->hasOne(RestaurantPaymentLog::class, 'menu_order_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function menu_details()
    {
        return $this->hasMany(MenuOrderDetail::class, 'menu_order_id','id');
    }

    public function order_attributes()
    {
        return $this->hasManyThrough(MenuOrderDetailAttribute::class, MenuOrderDetail::class);
    }

}
