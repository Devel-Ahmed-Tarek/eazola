<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;

class AppearanceHubController extends Controller
{
    public function index()
    {
        return view('tenant.admin.appearance-hub.index');
    }
}

