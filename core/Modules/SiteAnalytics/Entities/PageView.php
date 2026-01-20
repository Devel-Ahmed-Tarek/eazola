<?php

namespace Modules\SiteAnalytics\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PageView extends Model
{
    /** @var array */
    protected $fillable = [
        'session',
        'uri',
        'source',
        'country',
        'browser',
        'device',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ];

    protected $dates = ['created_at'];

    public function setSourceAttribute($value): void
    {
        $this->attributes['source'] = $value
            ? preg_replace('/https?:\/\/(www\.)?([a-z\-\.]+)\/?.*/i', '$2', $value)
            : $value;
    }

    public function setCountryAttribute($value): void
    {
        $this->attributes['country'] = extension_loaded('intl') ? \Locale::getDisplayRegion($value, 'en') : 'en';
    }

    public function getTypeAttribute($value): string
    {
        return ucfirst($value);
    }

    public function scopeFilter($query, $period = 'today')
    {
        if (! in_array($period, ['today', 'yesterday'])) {
            [$interval, $unit] = explode('_', $period);

            return $query->where('created_at', '>=', now()->sub($unit, $interval));
        }

        if ($period === 'yesterday') {
            return $query->whereDate('created_at', today()->subDay()->toDateString());
        }

        return $query->whereDate('created_at', today());
    }

    public function scopeChart($query, $period = 'today')
    {
        if ($period === 'yesterday') {
            return $query->whereDate('created_at', today()->subDay())->select(DB::raw("DATE_FORMAT(created_at, '%h %p') as time"), DB::raw('count(*) as total_views'));
        }

        if (in_array($period, ['1_week', '30_days'])) {
            $days = $period == '1_week' ? 7 : 30;

            $queryExt = $query->whereBetween('created_at', [today()->subDays($days), today()->endOfWeek()]);
            if ($days == 7)
            {
                $queryExt->select(DB::raw("DATE_FORMAT(created_at, '%W') as time"), DB::raw('count(*) as total_views'));
            } else {
                $queryExt->select(DB::raw("DATE_FORMAT(created_at, '%D %M') as time"), DB::raw('count(*) as total_views'));
            }

            return $queryExt;
        }


        if (in_array($period, ['6_months', '12_months'])) {
            $months = $period == '6_months' ? 6 : 12;

            return $query->whereBetween('created_at', [today()->subMonths($months), today()->endOfMonth()])->select(DB::raw("DATE_FORMAT(created_at, '%b') as time"), DB::raw('count(*) as total_views'));
        }

        return $query->whereDate('created_at', today())->select(DB::raw("DATE_FORMAT(created_at, '%h %p') as time"), DB::raw('count(*) as total_views'));
    }

    public function scopeChartProduct($query, $period = 'today')
    {
        if ($period === 'yesterday') {
            return $query->whereDate('created_at', today()->subDay())->select(DB::raw("DATE_FORMAT(created_at, '%h %p') as time"), DB::raw('count(*) as total_views'));
        }

        if (in_array($period, ['1_week', '30_days'])) {
            $days = $period == '1_week' ? 7 : 30;

            $queryExt = $query->whereBetween('created_at', [today()->subDays($days), today()->endOfWeek()]);
            if ($days == 7)
            {
                $queryExt->select(DB::raw("DATE_FORMAT(created_at, '%W') as time"), DB::raw('count(*) as total_views'));
            } else {
                $queryExt->select(DB::raw("DATE_FORMAT(created_at, '%D %M') as time"), DB::raw('count(*) as total_views'));
            }

            return $queryExt;
        }


        if (in_array($period, ['6_months', '12_months'])) {
            $months = $period == '6_months' ? 6 : 12;

            return $query->whereBetween('created_at', [today()->subMonths($months), today()->endOfMonth()])->select(DB::raw("DATE_FORMAT(created_at, '%b') as time"), DB::raw('count(*) as total_views'));
        }

        return $query->whereDate('created_at', today())->select(DB::raw("DATE_FORMAT(created_at, '%h %p') as time"), DB::raw('count(*) as total_views'));
    }

    public function scopeUserType($query)
    {
        if (tenant())
        {
            return $query->where('uri', 'LIKE', '%/shop/product/%');
        }

        return $query->where('uri', 'LIKE', '%plan-order/%')->orWhere('uri', 'LIKE', '%view-plan/%');
    }
}
