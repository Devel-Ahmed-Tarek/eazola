<?php

namespace Modules\SiteAnalytics\Http\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\OrderProducts;
use Modules\SiteAnalytics\Entities\PageView;

class SiteAnalyticsService
{
    public function __construct(private $period)
    {

    }

    public function periods(): array
    {
        return [
            'today'     => __('Today'),
            'yesterday' => __('Yesterday'),
            '1_week'    => __('Last 7 days'),
            '30_days'   => __('Last 30 days'),
            '6_months'  => __('Last 6 months'),
            '12_months' => __('Last 12 months'),
        ];
    }

    public function stats(): array
    {
        $data = [];
        if (get_static_option('site_analytics_unique_user'))
        {
            $data[] = [
                    'key'   => __('Unique Users'),
                    'value' => PageView::query()
                        ->scopes(['filter' => [$this->period]])
                        ->groupBy('session')
                        ->pluck('session')
                        ->count(),
                ];
        }

        if (get_static_option('site_analytics_page_view'))
        {
            $data[] =
                [
                    'key'   => __('Page Views'),
                    'value' => PageView::query()
                        ->scopes(['filter' => [$this->period]])
                        ->count(),
                ];
        }

        return $data;
    }

    public function pages(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period]])
            ->select('uri as page', DB::raw('count(*) as users'))
            ->groupBy('page')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function pagesByPlan(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period], 'userType'])
            ->select('uri as page', DB::raw('count(*) as users'))
            ->groupBy('page')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function pagesByDate(): Collection
    {
        return PageView::query()
            ->scopes(['chart' => [$this->period]])
            ->groupBy('time')
            ->orderBy('time')
            ->get();
    }

    public function sources(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period]])
            ->select('source as page', DB::raw('count(*) as users'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function sourcesByPlan(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period], 'userType'])
            ->select('source as page', DB::raw('count(*) as users'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function users(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period]])
            ->select('country', DB::raw('count(*) as users'))
            ->groupBy('country')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function usersByPlan(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period], 'userType'])
            ->select('country', DB::raw('count(*) as users'))
            ->groupBy('country')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function devices(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period]])
            ->select('device as type', DB::raw('count(*) as users'))
            ->groupBy('type')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function devicesByPlan(): Collection
    {
        return PageView::query()
            ->scopes(['filter' => [$this->period], 'userType'])
            ->select('device as type', DB::raw('count(*) as users'))
            ->groupBy('type')
            ->orderBy('users', 'desc')
            ->get();
    }

    public function utm(): Collection
    {
        return collect([
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
        ])->mapWithKeys(fn (string $key) => [$key => [
            'key'   => $key,
            'items' => PageView::query()
                ->select([$key, DB::raw('count(*) as count')])
                ->scopes(['filter' => [$this->period]])
                ->whereNotNull($key)
                ->groupBy($key)
                ->orderBy('count', 'desc')
                ->get()
                ->map(fn ($item) => [
                    'value' => $item->{$key},
                    'count' => $item->count,
                ]),
        ]])->filter(fn (array $set) => $set['items']->count() > 0);
    }

    // TENANT
    public function pagesByProduct(): Collection
    {
        return PageView::query()
            ->scopes(['chartProduct' => [$this->period], 'userType'])
            ->groupBy('time')
            ->orderBy('time')
            ->get();
    }

    public function orders()
    {
        return OrderProducts::query()
            ->scopes(['chart' => [$this->period]])
            ->groupBy('time')
            ->orderBy('time')
            ->get();
    }

    public static function sortElements($elements) {
        $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        if (in_array(current($elements), $days)) {
            $reference = array_flip($days);
        } elseif (in_array(current($elements), $months)) {
            $reference = array_flip($months);
        } elseif (preg_match('/^(\d{2} [APMapm]{2})$/', current($elements)) || preg_match('/^(\d+)(AM|PM)$/', current($elements))) {
            usort($elements, function ($a, $b) {
                return strtotime($a) <=> strtotime($b);
            });
            return $elements;
        } else {
            return "Invalid elements.";
        }

        usort($elements, function ($a, $b) use ($reference) {
            return $reference[$a] <=> $reference[$b];
        });

        return $elements;
    }
}
