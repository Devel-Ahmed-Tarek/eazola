<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;

class MarketingHubController extends Controller
{
    public function index()
    {
        return view('tenant.admin.marketing-hub.index');
    }
}

