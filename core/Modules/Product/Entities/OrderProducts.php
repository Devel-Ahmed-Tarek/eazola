<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Modules\Campaign\Entities\CampaignProduct;
use Modules\ShippingModule\Entities\UserShippingAddress;

class OrderProducts extends Model
{
    use HasFactory;

    protected $table = 'order_products';

    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'quantity'
    ];

    public function campaign_product(): HasOne
    {
        return $this->hasOne(CampaignProduct::class, 'product_id', 'product_id');
    }

    public function scopeChart($query, $period = 'today')
    {
        if ($period === 'yesterday') {
            return $query->whereDate('created_at', today()->subDay())->select(DB::raw("DATE_FORMAT(created_at, '%h %p') as time"), DB::raw('count(*) as total_sale'));
        }

        if (in_array($period, ['1_week', '30_days'])) {
            $days = $period == '1_week' ? 7 : 30;

            $queryExt = $query->whereBetween('created_at', [today()->subDays($days), today()->endOfWeek()]);
            if ($days == 7)
            {
                $queryExt->select(DB::raw("DATE_FORMAT(created_at, '%W') as time"), DB::raw('count(*) as total_sale'));
            } else {
                $queryExt->select(DB::raw("DATE_FORMAT(created_at, '%D %M') as time"), DB::raw('count(*) as total_sale'));
            }
            return $queryExt;
        }


        if (in_array($period, ['6_months', '12_months'])) {
            $months = $period == '6_months' ? 6 : 12;

            return $query->whereBetween('created_at', [today()->subMonths($months), today()->endOfMonth()])->select(DB::raw("DATE_FORMAT(created_at, '%b') as time"), DB::raw('count(*) as total_sale'));
        }

        return $query->whereDate('created_at', today())->select(DB::raw("DATE_FORMAT(created_at, '%h %p') as time"), DB::raw('count(*) as total_sale'));
    }
}
