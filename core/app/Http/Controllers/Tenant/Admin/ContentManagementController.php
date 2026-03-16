<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;

class ContentManagementController extends Controller
{
    public function index()
    {
        return view('tenant.admin.content-management.index');
    }
}

