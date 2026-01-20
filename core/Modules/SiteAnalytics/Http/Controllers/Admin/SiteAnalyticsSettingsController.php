<?php

namespace Modules\SiteAnalytics\Http\Controllers\Admin;

use App\Helpers\FlashMsg;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\SiteAnalytics\Http\Services\SiteAnalyticsService;

class SiteAnalyticsSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'today');
        $service = (new SiteAnalyticsService($period));


        return view('siteanalytics::admin.dashboard', [
            'period'  => $period,
            'periods' => $service->periods(),
            'stats'   => $service->stats(),
            'pages'   => $service->pages(),
            'sources' => $service->sources(),
            'users'   => $service->users(),
            'devices' => $service->devices(),
            'utm'     => $service->utm(),
            'pages_charts'     => $service->pagesByDate(),
        ]);
    }

    public function analytics(Request $request)
    {
        $period = $request->get('period', 'today');
        $service = (new SiteAnalyticsService($period));

        return view('siteanalytics::admin.analytics', [
            'period'  => $period,
            'periods' => $service->periods(),
            'pages'   => tenant() ? $service->pagesByProduct() : $service->pagesByPlan(),
            'sources' => $service->sourcesByPlan(),
            'users'   => $service->usersByPlan(),
            'devices' => $service->devicesByPlan(),
            'products_views' => tenant() ? $service->pagesByPlan() : [],
            'orders' => tenant() ? $service->orders() : []
        ]);
    }

    public function settings()
    {
        return view('siteanalytics::admin.settings');
    }

    public function update_settings(Request $request)
    {
        $requested_params = [
            'site_analytics_status' => 'nullable',
            'site_analytics_unique_user' => 'nullable',
            'site_analytics_page_view' => 'nullable',
            'site_analytics_view_source' => 'nullable',
            'site_analytics_users_country' => 'nullable',
            'site_analytics_users_device' => 'nullable',
            'site_analytics_users_browser' => 'nullable'
        ];

        if (tenant())
        {
            $tenant_requested_params = [
                'site_analytics_most_viewed_products' => 'nullable',
                'site_analytics_most_sold_products' => 'nullable',
                'site_analytics_purchase_bounce_rate' => 'nullable',
            ];

            $requested_params = array_merge($requested_params, $tenant_requested_params);
        }

        $request->validate($requested_params);

        foreach ($requested_params as $key => $value)
        {
            update_static_option($key, $request->$key);
        }

        return back()->with(FlashMsg::update_succeed('Analytics Settings'));
    }
}
