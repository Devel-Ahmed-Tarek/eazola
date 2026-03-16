<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;

class GeneralSettingsHubController extends Controller
{
    public function index()
    {
        return view('tenant.admin.general-settings-hub.index');
    }
}

