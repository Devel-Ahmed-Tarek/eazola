<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuOrderDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\MenuOrderDetailFactory::new();
    }

    public function order_detail_attributes(){
        return $this->hasMany(MenuOrderDetailAttribute::class,'menu_order_detail_id','id');
    }
}
